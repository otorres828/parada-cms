<?php

namespace App\Services\Admin;

use App\Models\Empresa;
use App\Models\Movimiento;
use App\Models\Pago;
use App\Models\Reembolso;
use App\Models\Reserva;
use App\Models\Retiro;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class Finance
{
    private static function fail(string $message): never
    {
        throw ValidationException::withMessages(['monto' => $message]);
    }

    public static function balance(int $empresaId): array
    {
        $total = (string) Movimiento::searchAdmin('', ['empresa_id' => $empresaId])
            ->where('moneda', 'USD')
            ->sum('monto');
        $holds = (string) Retiro::searchAdmin('', ['empresa_id' => $empresaId])
            ->whereIn('estatus', ['pendiente', 'aprobado'])
            ->sum('monto');
        $refunds = (string) Reembolso::searchAdmin('', ['empresa_id' => $empresaId])
            ->whereIn('estatus', ['pendiente', 'aprobado'])
            ->join('pagos', 'pagos.id', '=', 'reembolsos.pago_id')
            ->sum('pagos.neto_empresa');

        return ['saldo' => bcadd($total, '0', 2), 'retenido' => bcadd($holds, $refunds, 2), 'disponible' => bcsub(bcsub($total, $holds, 2), $refunds, 2)];
    }

    public static function payment(array $data, ?string $proof): Pago
    {
        Access::authorize('pagos', 'add');
        Validator::make($data, ['reserva_id' => 'required|integer|exists:reservas,id', 'referencia' => 'required|string|max:255|unique:pagos,referencia', 'metodo' => 'required|in:transferencia,tarjeta,pasarela,efectivo', 'fecha_pago' => 'required|date|before_or_equal:now'])->validate();
        if (!Settings::get('payments', $data['metodo'], $data['metodo'] !== 'efectivo')) {
            self::fail('Este método de conciliación está deshabilitado.');
        }

        return DB::transaction(function () use ($data, $proof) {
            $snapshot = Reserva::with('programacion.viaje')->findOrFail($data['reserva_id']);
            $empresa = Empresa::whereKey($snapshot->programacion->viaje->empresa_id)
                ->lockForUpdate()
                ->firstOrFail();
            $reserva = Reserva::whereKey($snapshot->id)->lockForUpdate()->firstOrFail();
            if ($reserva->estado_pago !== Reserva::ESTADO_PAGO_PAGADO) {
                self::fail('Solo se pueden conciliar reservas que ya tienen el pago confirmado.');
            }
            if (Pago::where('reserva_id', $reserva->id)->exists()) {
                self::fail('Esta reserva ya tiene un pago registrado.');
            }
            $base = bcsub($reserva->monto_pasajes, $reserva->descuento_aplicado, 2);
            if (bccomp($base, '0', 2) < 0 || bccomp($reserva->monto_total, '0', 2) <= 0) {
                self::fail('Los importes de la reserva no son válidos.');
            }
            if (bccomp(bcadd($base, $reserva->tasa_servicio, 2), $reserva->monto_total, 2) !== 0) {
                self::fail('Revisa los importes y las tasas de servicio antes de conciliar.');
            }
            $tasaPasajes = $reserva->pasajes()->get()->reduce(function (string $total, $pasaje) {
                return bcadd($total, $pasaje->tasa_servicio, 2);
            }, '0.00');
            if (bccomp($tasaPasajes, $reserva->tasa_servicio, 2) !== 0) self::fail('La tasa de la reserva no coincide con la suma de sus pasajes.');
            $net = $base;
            $pago = Pago::create([
                'reserva_id' => $reserva->id,
                'empresa_id' => $empresa->id,
                'admin_id' => auth('admin')->id(),
                'monto' => $reserva->monto_total,
                'neto_empresa' => $net,
                'comision' => '0.00',
                'moneda' => 'USD',
                'referencia' => $data['referencia'],
                'metodo' => $data['metodo'],
                'fecha_pago' => $data['fecha_pago'],
                'comentario' => $data['comentario'] ?? null,
                'comprobante' => $proof,
            ]);
            Movimiento::create(['empresa_id' => $empresa->id, 'admin_id' => auth('admin')->id(), 'pago_id' => $pago->id, 'clave' => 'pago:' . $pago->id, 'tipo' => 'venta', 'monto' => $net, 'moneda' => 'USD', 'descripcion' => 'Pago recibido: ' . $pago->referencia]);
            Audit::record('pago.registrado', $pago);

            return $pago;
        });
    }

    public static function withdrawal(array $data): Retiro
    {
        Access::authorize('retiros', 'add');
        Validator::make($data, ['empresa_id' => 'required|integer|exists:empresas,id', 'monto' => 'required|decimal:0,2|min:0.01|max:9999999999.99', 'datos_bancarios' => 'required|string|max:2000'])->validate();
        if (!Settings::get('withdrawals', 'habilitados', true)) {
            self::fail('Las solicitudes de retiro están deshabilitadas.');
        }
        if (bccomp($data['monto'], Settings::get('withdrawals', 'minimo', '1.00'), 2) < 0 || bccomp($data['monto'], Settings::get('withdrawals', 'maximo', '10000.00'), 2) > 0) {
            self::fail('El importe está fuera de los límites de retiro configurados.');
        }

        return DB::transaction(function () use ($data) {
            $empresa = Empresa::whereKey($data['empresa_id'])->lockForUpdate()->firstOrFail();
            if (!$empresa->estatus || !$empresa->retiros_habilitados) {
                self::fail('Esta empresa no tiene retiros habilitados.');
            }
            if (bccomp(self::balance($empresa->id)['disponible'], $data['monto'], 2) < 0) {
                self::fail('El saldo disponible no cubre el retiro.');
            }
            $retiro = Retiro::create($data + ['admin_id' => auth('admin')->id(), 'moneda' => 'USD', 'estatus' => 'pendiente']);
            Audit::record('retiro.solicitado', $retiro);

            return $retiro;
        });
    }

    public static function refund(array $data): Reembolso
    {
        Access::authorize('reembolsos', 'add');
        Validator::make($data, ['pago_id' => 'required|integer|exists:pagos,id', 'motivo' => 'required|string|min:10|max:2000'])->validate();

        return DB::transaction(function () use ($data) {
            $snapshot = Pago::findOrFail($data['pago_id']);
            Empresa::whereKey($snapshot->empresa_id)->lockForUpdate()->firstOrFail();
            $pago = Pago::with('reserva')->whereKey($snapshot->id)->lockForUpdate()->firstOrFail();
            if ($pago->reserva->estado_pago !== Reserva::ESTADO_PAGO_PAGADO) {
                self::fail('La reserva no está pagada.');
            }
            if (Reembolso::where('pago_id', $pago->id)->exists()) {
                self::fail('Ya existe una solicitud para este pago.');
            }
            if (bccomp(self::balance($pago->empresa_id)['disponible'], $pago->neto_empresa, 2) < 0) {
                self::fail('El saldo de la empresa no cubre el reembolso.');
            }
            $record = Reembolso::create($data + ['empresa_id' => $pago->empresa_id, 'admin_id' => auth('admin')->id(), 'monto' => $pago->monto, 'moneda' => 'USD', 'estatus' => 'pendiente']);
            Audit::record('reembolso.solicitado', $record);

            return $record;
        });
    }

    public static function review(string $module, int $id, string $decision, string $comment, ?string $reference, ?string $proof): void
    {
        Access::authorize($module, 'review');
        abort_unless(in_array($module, ['retiros', 'reembolsos']), 403);
        if (!in_array($decision, ['aprobado', 'rechazado', 'pagado'], true)) {
            self::fail('Resolución inválida.');
        }
        $model = $module === 'retiros' ? Retiro::class : Reembolso::class;
        DB::transaction(function () use ($model, $id, $decision, $comment, $reference, $proof, $module) {
            $snapshot = $model::findOrFail($id);
            $empresa = Empresa::whereKey($snapshot->empresa_id)->lockForUpdate()->firstOrFail();
            $record = $model::whereKey($id)->lockForUpdate()->firstOrFail();
            $allowed = $record->estatus === 'pendiente' ? ['aprobado', 'rechazado'] : ($record->estatus === 'aprobado' ? ['pagado', 'rechazado'] : []);
            if (!in_array($decision, $allowed, true)) {
                self::fail('La solicitud ya cambió de estado. Actualiza la pantalla.');
            }
            if ($decision !== 'rechazado' && $module === 'retiros' && (!$empresa->estatus || !$empresa->retiros_habilitados)) {
                self::fail('La empresa tiene los retiros deshabilitados.');
            }
            if ($decision === 'pagado') {
                if (!$reference || !$proof) {
                    self::fail('Indica la referencia y adjunta el comprobante de la transferencia realizada.');
                }
                if ($model::where('referencia', $reference)->whereKeyNot($id)->exists()) {
                    self::fail('Esta referencia ya está registrada.');
                }
                $amount = $module === 'retiros' ? $record->monto : $record->pago->neto_empresa;
                if (bccomp(self::balance($empresa->id)['disponible'], '0', 2) < 0) {
                    self::fail('El saldo ya no cubre las solicitudes pendientes.');
                }
                Movimiento::create([
                    'empresa_id' => $empresa->id,
                    'admin_id' => auth('admin')->id(),
                    $module === 'retiros' ? 'retiro_id' : 'reembolso_id' => $record->id,
                    'clave' => $module . ':' . $record->id,
                    'tipo' => $module === 'retiros' ? 'retiro' : 'reembolso',
                    'monto' => bcsub('0', $amount, 2),
                    'moneda' => 'USD',
                    'descripcion' => 'Transferencia confirmada: ' . $reference,
                ]);
                if ($module === 'reembolsos') {
                    $reserva = Reserva::whereKey($record->pago->reserva_id)->lockForUpdate()->firstOrFail();
                    if ($reserva->estado_pago !== Reserva::ESTADO_PAGO_PAGADO) {
                        self::fail('La reserva ya no admite reembolso.');
                    }
                    $reserva->update(['estado_pago' => Reserva::ESTADO_PAGO_REEMBOLSADO]);
                }
                $record->referencia = $reference;
                $record->comprobante = $proof;
            }
            $record->estatus = $decision;
            $record->comentario = $comment;
            $record->revisado_por = auth('admin')->id();
            $record->fecha_resolucion = now();
            $record->save();
            Audit::record($module . '.' . $decision, $record, ['comentario' => $comment]);
        });
    }
}
