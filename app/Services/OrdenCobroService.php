<?php

namespace App\Services;

use App\Jobs\NotificarOrdenCobroEmitidaJob;
use App\Jobs\ReactivarEmpresaPorCobranzaJob;
use App\Models\Empresa;
use App\Models\OrdenCobro;
use App\Models\Reserva;
use App\Notifications\OrdenCobroNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;

class OrdenCobroService
{
    public function generar(Empresa $empresa, ?Carbon $fechaCorte = null, bool $notificar = true): ?OrdenCobro
    {
        return DB::transaction(function () use ($empresa, $fechaCorte, $notificar) {
            $empresa = Empresa::query()->lockForUpdate()->findOrFail($empresa->id);

            if ($empresa->tipo_contrato !== Empresa::CONTRATO_ELLOS_RECIBEN || ! $empresa->dia_corte) {
                return null;
            }

            $abierta = $empresa->ordenesCobro()
                ->where('estatus', '!=', OrdenCobro::ESTATUS_APROBADO)
                ->lockForUpdate()
                ->exists();

            if ($abierta) {
                return null;
            }

            $periodoHasta = ($fechaCorte ?? now())
                ->copy()
                ->setTimeFromTimeString($empresa->hora_corte ?? '00:00:00')
                ->subSecond();

            $ultimaOrden = $empresa->ordenesCobro()->latest('periodo_hasta')->first();

            if ($ultimaOrden && $ultimaOrden->periodo_hasta->greaterThanOrEqualTo($periodoHasta)) {
                return null;
            }

            $periodoDesde = $ultimaOrden
                ? $ultimaOrden->periodo_hasta->copy()->addSecond()
                : $periodoHasta->copy()->subDays(7)->addSecond();

            $reservas = $this->reservasCobrables($empresa->id, $periodoDesde, $periodoHasta)->get();

            if ($reservas->isEmpty()) {
                return null;
            }

            $fechaEmision = now();
            $orden = OrdenCobro::create([
                'empresa_id' => $empresa->id,
                'codigo' => 'TMP-'.str()->random(20),
                'periodo_desde' => $periodoDesde,
                'periodo_hasta' => $periodoHasta,
                'fecha_emision' => $fechaEmision,
                'fecha_vencimiento' => $this->calcularVencimiento($empresa, $fechaEmision),
                'cantidad_reservas' => $reservas->count(),
                'total' => round((float) $reservas->sum('tasa_servicio'), 2),
                'estatus' => OrdenCobro::ESTATUS_EMITIDO,
                'reservas_incluidas' => $reservas->map(function (Reserva $reserva) {
                    return [
                        'reserva_id' => $reserva->id,
                        'codigo_referencia' => $reserva->codigo_referencia,
                        'tasa_servicio' => (float) $reserva->tasa_servicio,
                        'fecha_pago' => $reserva->fecha_pago?->toDateTimeString(),
                    ];
                })->values()->all(),
            ]);

            $orden->update([
                'codigo' => 'OC-'.$fechaEmision->format('Y').'-'.str_pad((string) $orden->id, 6, '0', STR_PAD_LEFT),
            ]);

            if ($notificar) {
                NotificarOrdenCobroEmitidaJob::dispatch($orden->id)->afterCommit();
            }

            return $orden->refresh();
        });
    }

    public function reportarPago(int $ordenCobroId, string $referencia, string $comprobante, ?string $comentario = null): OrdenCobro
    {
        return DB::transaction(function () use ($ordenCobroId, $referencia, $comprobante, $comentario) {
            $orden = OrdenCobro::query()->lockForUpdate()->findOrFail($ordenCobroId);

            if (! in_array($orden->estatus, [OrdenCobro::ESTATUS_EMITIDO, OrdenCobro::ESTATUS_RECHAZADO], true)) {
                throw ValidationException::withMessages(['orden' => 'La orden no admite un nuevo comprobante.']);
            }

            $orden->referencia_pago = $referencia;
            $orden->comprobante = $comprobante;
            $orden->fecha_pago_reportado = now();
            $orden->estatus = OrdenCobro::ESTATUS_PENDIENTE;

            if ($comentario) {
                $orden->comentarios = $this->agregarComentario($orden->comentarios, 'Empresa', $comentario);
            }

            $orden->save();

            return $orden->refresh();
        });
    }

    public function rechazar(int $ordenCobroId, int $adminId, string $motivo): OrdenCobro
    {
        return DB::transaction(function () use ($ordenCobroId, $adminId, $motivo) {
            $orden = OrdenCobro::query()->lockForUpdate()->findOrFail($ordenCobroId);

            if ($orden->estatus !== OrdenCobro::ESTATUS_PENDIENTE) {
                throw ValidationException::withMessages(['orden' => 'Solo se puede rechazar una orden pendiente.']);
            }

            $orden->admin_id = $adminId;
            $orden->estatus = OrdenCobro::ESTATUS_RECHAZADO;
            $orden->comentarios = $this->agregarComentario($orden->comentarios, 'Administración', $motivo);
            $orden->save();

            DB::afterCommit(function () use ($orden) {
                Notification::route('mail', $orden->empresa->email)
                    ->notify(new OrdenCobroNotification($orden->id, 'rechazo'));
            });

            return $orden->refresh();
        });
    }

    public function aprobar(int $ordenCobroId, int $adminId): OrdenCobro
    {
        $orden = DB::transaction(function () use ($ordenCobroId, $adminId) {
            $orden = OrdenCobro::query()->lockForUpdate()->findOrFail($ordenCobroId);

            if ($orden->estatus !== OrdenCobro::ESTATUS_PENDIENTE) {
                throw ValidationException::withMessages(['orden' => 'Solo se puede aprobar una orden pendiente.']);
            }

            $orden->admin_id = $adminId;
            $orden->estatus = OrdenCobro::ESTATUS_APROBADO;
            $orden->fecha_aprobacion = now();
            $orden->save();

            return $orden->refresh();
        });

        ReactivarEmpresaPorCobranzaJob::dispatch($orden->empresa_id);

        return $orden;
    }

    public function suspenderSiCorresponde(Empresa $empresa): bool
    {
        $tieneVencida = $empresa->ordenesCobro()
            ->where('fecha_vencimiento', '<', now())
            ->where(function ($query) {
                $query->whereIn('estatus', [OrdenCobro::ESTATUS_EMITIDO, OrdenCobro::ESTATUS_RECHAZADO]);
                $query->orWhere(function ($query) {
                    $query->where('estatus', OrdenCobro::ESTATUS_PENDIENTE);
                    $query->whereColumn('fecha_pago_reportado', '>', 'fecha_vencimiento');
                });
            })
            ->exists();

        if (! $tieneVencida) {
            return false;
        }

        $empresa->update(['bloqueada_por_cobranza_at' => $empresa->bloqueada_por_cobranza_at ?? now()]);

        return true;
    }

    public function reactivarSiCorresponde(Empresa $empresa): bool
    {
        $tieneVencida = $empresa->ordenesCobro()
            ->where('fecha_vencimiento', '<', now())
            ->where(function ($query) {
                $query->whereIn('estatus', [OrdenCobro::ESTATUS_EMITIDO, OrdenCobro::ESTATUS_RECHAZADO]);
                $query->orWhere(function ($query) {
                    $query->where('estatus', OrdenCobro::ESTATUS_PENDIENTE);
                    $query->whereColumn('fecha_pago_reportado', '>', 'fecha_vencimiento');
                });
            })
            ->exists();

        if ($tieneVencida || ! $empresa->bloqueada_por_cobranza_at) {
            return false;
        }

        $empresa->update(['bloqueada_por_cobranza_at' => null]);

        return true;
    }

    private function reservasCobrables(int $empresaId, Carbon $desde, Carbon $hasta)
    {
        return Reserva::query()
            ->select(['reservas.id', 'reservas.codigo_referencia', 'reservas.tasa_servicio', 'reservas.fecha_pago'])
            ->whereHas('programacion.viaje', function ($query) use ($empresaId) {
                $query->where('empresa_id', $empresaId);
            })
            ->whereBetween('reservas.fecha_pago', [$desde, $hasta])
            ->whereIn('reservas.estado_pago', [
                Reserva::ESTADO_PAGO_PAGADO,
                Reserva::ESTADO_PAGO_REPROGRAMADO,
                Reserva::ESTADO_PAGO_REEMBOLSADO,
            ])
            ->where('reservas.tasa_servicio', '>', 0)
            ->orderBy('reservas.id');
    }

    private function calcularVencimiento(Empresa $empresa, Carbon $emision): Carbon
    {
        $diasHastaVencimiento = ((int) $empresa->dia_vencimiento - $emision->dayOfWeekIso + 7) % 7;

        if ($diasHastaVencimiento === 0) {
            $diasHastaVencimiento = 7;
        }

        $vencimiento = $emision->copy()->addDays($diasHastaVencimiento);

        return $vencimiento->setTimeFromTimeString($empresa->hora_vencimiento ?? '23:59:59');
    }

    private function agregarComentario(?string $actual, string $autor, string $comentario): string
    {
        $entrada = '['.now()->format('d/m/Y H:i').'] '.$autor.':'.PHP_EOL.trim($comentario);

        return $actual ? $actual.PHP_EOL.PHP_EOL.$entrada : $entrada;
    }
}
