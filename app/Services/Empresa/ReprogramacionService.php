<?php

namespace App\Services\Empresa;

use App\Models\DatoBancario;
use App\Models\PagoReserva;
use App\Models\Pasaje;
use App\Models\Programacion;
use App\Models\ProgramacionTramoPrecio;
use App\Models\Reserva;
use App\Models\Terminal;
use App\Models\TipoCambio;
use App\Models\UsuarioEmpresa;
use Illuminate\Support\Facades\DB;

class ReprogramacionService
{
    public static function original(int $empresaId, int $id): Reserva
    {
        return Reserva::searchAdmin('', ['empresa_id' => $empresaId])
            ->with(['pasajes', 'tramoPrecio', 'pagos'])->findOrFail($id);
    }

    public static function validarOriginal(Reserva $reserva): void
    {
        Reserva::exigir($reserva->reprogramacion_id === null, 'reservaId', 'Esta reserva ya es una reprogramacion, no se puede volver a reprogramar.');
        Reserva::exigir($reserva->estado_pago !== Reserva::ESTADO_PAGO_REPROGRAMADO, 'reservaId', 'Esta reserva ya fue reprogramada y no puede reprogramarse nuevamente.');
        Reserva::exigir($reserva->estado_pago === Reserva::ESTADO_PAGO_PAGADO, 'reservaId', 'Solo se pueden reprogramar reservas pagadas.');
        Reserva::exigir($reserva->pasajes->isNotEmpty(), 'reservaId', 'La reserva no tiene pasajeros.');
        Reserva::exigir(! $reserva->pasajes->contains(function ($pasaje) {
            return $pasaje->abordado || $pasaje->hora_abordaje !== null;
        }), 'reservaId', 'Todos los pasajeros deben estar sin abordar.');
        $salida = $reserva->tramoPrecio?->getSalida();
        Reserva::exigir($salida !== null && now()->lessThanOrEqualTo($salida->copy()->endOfDay()->addDay()), 'reservaId', 'El plazo para reprogramar venció: solo se permite hasta el final del día siguiente a la fecha del viaje.');
        Reserva::exigir(! $reserva->tieneReprogramacionActiva(), 'reservaId', 'Esta reserva ya tiene una reprogramación activa.');
    }

    public static function subtotal(Reserva $original, ProgramacionTramoPrecio $tarifa): string
    {
        $total = '0.00';
        foreach ($original->pasajes as $pasaje) {
            $precio = $pasaje->numero_asiento !== null ? $tarifa->precio : '0.00';
            Reserva::exigir(bccomp($precio, $pasaje->descuento, 2) >= 0, 'tarifaId', 'El descuento original supera el precio de la nueva salida.');
            $total = bcadd($total, bcsub($precio, $pasaje->descuento, 2), 2);
        }

        return $total;
    }

    public static function registrar(UsuarioEmpresa $usuario, int $originalId, int $tarifaId, array $pagos, string $codigo): Reserva
    {
        abort_unless($usuario->hasPermission('reprogramaciones', 'add'), 403);

        return DB::transaction(function () use ($usuario, $originalId, $tarifaId, $pagos, $codigo) {
            $original = self::original($usuario->empresa_id, $originalId);
            $tarifa = ProgramacionTramoPrecio::with('programacion.viaje')->findOrFail($tarifaId);
            abort_unless($tarifa->programacion->viaje->empresa_id === $usuario->empresa_id, 404);
            $ids = array_unique([$original->programacion_id, $tarifa->programacion_id]);
            sort($ids);
            foreach ($ids as $id) {
                Programacion::bloquear($id);
            }
            $original = Reserva::query()->lockForUpdate()->findOrFail($original->id)->load(['pasajes', 'tramoPrecio']);
            $existente = $original->reservasReprogramadas()->where('codigo_referencia', $codigo)->first();
            if ($existente !== null) {
                return $existente->detalle();
            }
            self::validarOriginal($original);
            Reserva::exigir(! Reserva::where('codigo_referencia', $codigo)->exists(), 'reservaId', 'El código ya fue utilizado. Vuelve a abrir el formulario.');
            $tarifa = ProgramacionTramoPrecio::whereKey($tarifaId)->lockForUpdate()->firstOrFail();
            Reserva::exigir($tarifa->programacion_id !== $original->programacion_id, 'tarifaId', 'Selecciona una salida distinta de la original.');
            Reserva::exigir($tarifa->origen_terminal_id === $original->origen_terminal_id && $tarifa->destino_terminal_id === $original->destino_terminal_id, 'tarifaId', 'La reprogramación debe conservar el origen y el destino.');
            $programacion = Programacion::paraTaquilla($usuario->empresa_id, soloFuturas: false)->findOrFail($tarifa->programacion_id);
            ReservaTaquillaService::validarSalida($programacion, $usuario, $tarifa);
            $subtotal = self::subtotal($original, $tarifa);
            $anterior = bcsub($original->monto_pasajes, $original->descuento_aplicado, 2);
            Reserva::exigir(bccomp($subtotal, $anterior, 2) >= 0, 'tarifaId', 'No se puede reprogramar a un importe menor al subtotal original.');
            $diferencia = bcsub($subtotal, $anterior, 2);
            $cambio = TipoCambio::vigente();
            Reserva::exigir($cambio !== null && (float) $cambio->valor_usd > 0, 'pagos', 'No hay una tasa de cambio válida.');
            $disponibilidad = Pasaje::disponibilidad($programacion, $tarifa, Terminal::obtenerSecuenciaRuta($programacion));
            $cantidad = $original->pasajes->whereNotNull('numero_asiento')->count();
            Reserva::exigir($cantidad <= $disponibilidad['cupo_tramo'] && $cantidad <= count($disponibilidad['asientos']), 'tarifaId', 'No hay puestos para todos los pasajeros.');
            $reserva = $original->replicate();
            $reserva->fill([
                'programacion_id' => $programacion->id,
                'programacion_tramo_precio_id' => $tarifa->id,
                'reprogramacion_id' => $original->id,
                'codigo_referencia' => $codigo,
                'tipos_cambios_id' => $cambio->id,
                'cupon_id' => null,
                'exoneracion_tasa_json' => null,
                'monto_pasajes' => bcmul($tarifa->precio, (string) $cantidad, 2),
                'tasa_servicio' => '0.00',
                'monto_total' => $subtotal,
                'estado_pago' => Reserva::ESTADO_PAGO_PAGADO,
                'fecha_compra' => now(),
                'fecha_pago' => now(),
                'fecha_expiracion' => null,
            ]);
            $reserva->save();
            $asientos = $disponibilidad['asientos'];
            foreach ($original->pasajes as $pasaje) {
                $nuevo = $pasaje->replicate();
                $precio = $pasaje->numero_asiento !== null ? $tarifa->precio : '0.00';
                $nuevo->fill([
                    'reserva_id' => $reserva->id,
                    'numero_asiento' => $pasaje->numero_asiento !== null ? array_shift($asientos) : null,
                    'precio_base' => $precio,
                    'subtotal' => bcsub($precio, $pasaje->descuento, 2),
                    'tasa_servicio' => '0.00',
                    'total' => bcsub($precio, $pasaje->descuento, 2),
                    'servicio_json' => null,
                    'localizador' => null,
                    'abordado' => false,
                    'hora_abordaje' => null,
                ]);
                $nuevo->save();
            }
            $suma = '0.00';
            foreach ($pagos as $pago) {
                $datos = PagoTaquillaService::validarPago($pago);
                if ((int) $datos['tipo'] === PagoReserva::TIPO_PAGO_MOVIL) {
                    DatoBancario::where('empresa_id', $usuario->empresa_id)->where('estatus', DatoBancario::ACTIVO)
                        ->where('tipo', DatoBancario::TIPO_PAGO_MOVIL)->findOrFail($datos['cuenta_id']);
                }
                if ((int) $datos['tipo'] !== PagoReserva::TIPO_PAGO_EFECTIVO) {
                    Reserva::exigir(! PagoReserva::where('referencia_pago', $datos['referencia'])->exists(), 'pagos', 'La referencia de pago ya fue registrada.');
                }
                $monto = $datos['moneda'] === 'VES'
                    ? number_format((float) $datos['monto'] / (float) $cambio->valor_usd, 2, '.', '')
                    : (string) $datos['monto'];
                $suma = bcadd($suma, $monto, 2);
                $reserva->pagos()->create([
                    'tipo_pago' => $datos['tipo'],
                    'monto_recibido' => $monto,
                    'total' => $monto,
                    'tasa_servicio' => '0.00',
                    'metodo_pago' => (int) $datos['tipo'] === PagoReserva::TIPO_PAGO_MOVIL ? $datos['cuenta_id'] : null,
                    'referencia_pago' => (int) $datos['tipo'] === PagoReserva::TIPO_PAGO_EFECTIVO ? 'EF-'.Reserva::generarLocalizador(7) : $datos['referencia'],
                    'fecha_pago' => now(),
                ]);
            }
            Reserva::exigir(bccomp($suma, $diferencia, 2) === 0, 'pagos', 'Los pagos deben cubrir exactamente la diferencia de precio.');
            $original->update(['estado_pago' => Reserva::ESTADO_PAGO_REPROGRAMADO, 'fecha_expiracion' => null]);

            return $reserva->detalle();
        }, 3);
    }
}
