<?php

namespace App\Services;

use App\Models\DatoBancario;
use App\Models\PagoReserva;
use App\Models\Pasaje;
use App\Models\Programacion;
use App\Models\Reserva;
use App\Models\Terminal;
use App\Models\User;
use Closure;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PagoReservaService
{
    // El cliente reporta el pago y la reserva queda pendiente de verificación.
    public static function pasarAPendiente(
        User $cliente,
        int $reservaId,
        int $metodoPagoId,
        string $referenciaPago,
        string $fechaPago,
        ?string $comprobante = null,
    ): Reserva {
        Validator::make(compact('metodoPagoId', 'referenciaPago', 'fechaPago', 'comprobante'), [
            'metodoPagoId' => ['required', 'integer'],
            'referenciaPago' => ['required', 'string', 'max:255'],
            'fechaPago' => ['required', 'date', 'before_or_equal:now'],
            'comprobante' => ['nullable', 'string', 'max:255'],
        ], [], [
            'metodoPagoId' => 'Método de pago',
            'referenciaPago' => 'Referencia de pago',
            'fechaPago' => 'Fecha de pago',
        ])->validate();

        return self::conReserva($cliente, $reservaId, function ($reserva) use ($metodoPagoId, $referenciaPago, $fechaPago, $comprobante) {
            if ($reserva->estado_pago === Reserva::ESTADO_PAGO_PENDIENTE) {
                $pago = $reserva->pago()->first();
                PagoReserva::exigir(
                    $pago?->metodo_pago === $metodoPagoId
                        && $pago?->referencia_pago === $referenciaPago,
                    'referenciaPago',
                    'La reserva ya tiene otro pago pendiente.',
                );

                return $reserva->detalle();
            }

            $datoBancario = DatoBancario::query()
                ->whereKey($metodoPagoId)
                ->where('estatus', DatoBancario::ACTIVO)
                ->first();

            DatoBancario::exigir(
                $datoBancario !== null,
                'metodoPagoId',
                'El método de pago no está activo.',
            );

            $reserva->validarEditable();
            $programacion = Programacion::bloquear($reserva->programacion_id);
            $reserva->validarVigente();
            Terminal::validarSalida($programacion, $reserva->origen_terminal_id, Terminal::obtenerSecuenciaRuta($programacion));
            Pasaje::validarPasajeros($reserva);
            app(CuponService::class)->validarCuponAplicado($reserva);
            PagoReserva::exigir(
                ! PagoReserva::where('referencia_pago', $referenciaPago)->exists(),
                'referenciaPago',
                'La referencia de pago ya fue registrada.',
            );

            $reserva = TasasServicioService::calcularTasasReserva($reserva);
            $reserva->update([
                'estado_pago' => Reserva::ESTADO_PAGO_PENDIENTE,
                'fecha_expiracion' => null,
            ]);
            PagoReserva::create([
                'reserva_id' => $reserva->id,
                'total' => $reserva->monto_total,
                'tasa_servicio' => $reserva->tasa_servicio,
                'metodo_pago' => $metodoPagoId,
                'referencia_pago' => $referenciaPago,
                'fecha_pago' => $fechaPago,
                'comprobante' => $comprobante,
            ]);

            return $reserva->detalle();
        });
    }

    // Uso exclusivo del backend después de verificar el cobro con el banco o la pasarela.
    public static function confirmarPago(
        int $reservaId,
        string $montoConfirmado,
        string $moneda = 'USD',
    ): Reserva {
        Validator::make(['monto' => $montoConfirmado, 'moneda' => $moneda], [
            'monto' => ['required', 'decimal:0,2', 'min:0', 'max:9999999999.99'],
            'moneda' => ['required', 'in:USD'],
        ])->validate();

        return self::conReserva(null, $reservaId, function ($reserva) use ($montoConfirmado) {
            $pago = $reserva->pago()->first();
            PagoReserva::exigir(
                $pago !== null,
                'pago',
                'La reserva no tiene un pago registrado.',
            );
            Reserva::exigir(
                bccomp($reserva->monto_total, $montoConfirmado, 2) === 0
                    && bccomp($pago->total, $montoConfirmado, 2) === 0,
                'pago',
                'El cobro no coincide con el total de la reserva.',
            );

            if ($reserva->estado_pago === Reserva::ESTADO_PAGO_PAGADO) {
                return $reserva->detalle();
            }

            Reserva::exigir(
                $reserva->estado_pago === Reserva::ESTADO_PAGO_PENDIENTE,
                'reserva',
                'La reserva no tiene un pago pendiente.',
            );
            Pasaje::validarPasajeros($reserva);
            app(CuponService::class)->validarCuponAplicado($reserva);
            $reserva->update([
                'estado_pago' => Reserva::ESTADO_PAGO_PAGADO,
                'fecha_pago' => now(),
            ]);

            if ($reserva->esReprogramacion()) {
                $reservaOriginal = Reserva::query()
                    ->lockForUpdate()
                    ->findOrFail($reserva->reprogramacion_id);
                Reserva::exigir(
                    $reservaOriginal->estado_pago === Reserva::ESTADO_PAGO_PAGADO,
                    'reprogramacion',
                    'La reserva original ya no puede ser reprogramada.',
                );
                $reservaOriginal->update([
                    'estado_pago' => Reserva::ESTADO_PAGO_REPROGRAMADO,
                    'fecha_expiracion' => null,
                ]);
            }

            return $reserva->detalle();
        });
    }

    public static function marcarPagoFallido(int $reservaId): Reserva
    {
        return self::conReserva(null, $reservaId, function ($reserva) {
            Reserva::exigir(
                in_array($reserva->estado_pago, [
                    Reserva::ESTADO_PAGO_PENDIENTE,
                    Reserva::ESTADO_PAGO_FALLIDO,
                ], true),
                'reserva',
                'No se puede marcar este pago como fallido.',
            );
            $reserva->update(['estado_pago' => Reserva::ESTADO_PAGO_FALLIDO]);
            app(CuponService::class)->cancelarYLiberarCupon($reserva);

            return $reserva->detalle();
        });
    }

    private static function conReserva(?User $cliente, int $reservaId, Closure $accion): Reserva
    {
        return DB::transaction(function () use ($cliente, $reservaId, $accion) {
            $consulta = Reserva::whereKey($reservaId);

            if ($cliente !== null) {
                $consulta->where('usuario_id', $cliente->id);
            }

            return $accion($consulta->lockForUpdate()->firstOrFail());
        }, 3);
    }
}
