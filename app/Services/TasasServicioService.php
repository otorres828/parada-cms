<?php

namespace App\Services;

use App\Models\ExoneracionTasaServicio;
use App\Models\Programacion;
use App\Models\Reserva;
use App\Models\TasaServicio;
use Illuminate\Validation\ValidationException;

class TasasServicioService
{
    public static function obtenerExoneracionTasa(
        Programacion $programacion,
        ?Reserva $reservaOriginal = null,
    ): ?array {
        if ($reservaOriginal !== null) {
            return [
                'exoneracion_id' => null,
                'fecha_desde' => null,
                'fecha_hasta' => null,
                'motivo' => 'Reprogramación de la reserva '.$reservaOriginal->codigo_referencia,
            ];
        }

        $empresaId = (int) $programacion->viaje->empresa_id;
        $exoneracion = ExoneracionTasaServicio::vigenteParaEmpresa($empresaId);

        return $exoneracion?->snapshot();
    }

    // La reserva llega bloqueada por ReservaService; aquí solo se recalculan sus importes.
    public static function calcularTasasReserva(Reserva $reserva): Reserva
    {
        if ($reserva->estado_pago !== Reserva::ESTADO_PAGO_NUEVO || $reserva->pago()->exists()) {
            throw ValidationException::withMessages([
                'reserva' => 'No se pueden recalcular tasas de una reserva cobrada o cerrada.',
            ]);
        }

        $pasajes = $reserva->pasajes()->get();

        if ($pasajes->isEmpty()) {
            throw ValidationException::withMessages([
                'pasajes' => 'La reserva debe tener al menos un pasaje.',
            ]);
        }

        $tasas = '0.00';
        $base = '0.00';
        $descuentos = '0.00';

        foreach ($pasajes as $pasaje) {
            if (bccomp($pasaje->precio_base, '0', 2) < 0
                || bccomp($pasaje->descuento, '0', 2) < 0
                || bccomp($pasaje->descuento, $pasaje->precio_base, 2) > 0) {
                throw ValidationException::withMessages([
                    'pasajes' => 'Importes del pasaje inválidos.',
                ]);
            }

            $pasaje->subtotal = bcsub($pasaje->precio_base, $pasaje->descuento, 2);

            if ($reserva->exoneracion_tasa_json !== null || $reserva->esReprogramacion()) {
                $tasaCalculada = '0.00';
                $pasaje->servicio_json = null;
            } elseif ($pasaje->servicio_json !== null) {
                $tasaCalculada = $pasaje->tasa_servicio;
            } else {
                $tasa = TasaServicio::paraPrecio($pasaje->subtotal);
                $tasaCalculada = $tasa->calcular($pasaje->subtotal);
                $pasaje->servicio_json = [
                    'monto_minimo' => $tasa->monto_minimo,
                    'monto_maximo' => $tasa->monto_maximo,
                    'valor' => $tasa->cantidad,
                    'tipo_servicio' => $tasa->tipo_servicio,
                ];
            }

            $pasaje->tasa_servicio = $tasaCalculada;
            $pasaje->total = bcadd($pasaje->subtotal, $pasaje->tasa_servicio, 2);
            $pasaje->save();
            $tasas = bcadd($tasas, $pasaje->tasa_servicio, 2);
            $base = bcadd($base, $pasaje->precio_base, 2);
            $descuentos = bcadd($descuentos, $pasaje->descuento, 2);
        }

        $reserva->update([
            'monto_pasajes' => $base,
            'descuento_aplicado' => $descuentos,
            'tasa_servicio' => $tasas,
            'monto_total' => bcadd(bcsub($base, $descuentos, 2), $tasas, 2),
        ]);

        return $reserva;
    }
}
