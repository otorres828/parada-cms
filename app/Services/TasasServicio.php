<?php
namespace App\Services;
use App\Models\Reserva;
use App\Models\TasaServicio;
use App\Models\GroupAdmin;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
class TasasServicio
{
    // El checkout debe invocarlo dentro de la transacción que crea la reserva y sus pasajes, antes del pago.
    public static function aplicarReserva(int $reservaId): Reserva
    {
        return DB::transaction(function() use($reservaId) {
            GroupAdmin::where('url', 'administracion')->lockForUpdate()->firstOrFail();
            $reserva = Reserva::whereKey($reservaId)->lockForUpdate()->firstOrFail();
            if (!in_array($reserva->estado_pago, [Reserva::ESTADO_PAGO_NUEVO, Reserva::ESTADO_PAGO_PENDIENTE], true) || $reserva->pagos()->exists()) throw ValidationException::withMessages(['reserva'=>'No se pueden recalcular tasas de una reserva cobrada o cerrada.']);
            $pasajes = $reserva->pasajes()->lockForUpdate()->get();
            if ($pasajes->isEmpty()) throw ValidationException::withMessages(['pasajes'=>'La reserva debe tener al menos un pasaje.']);
            $tasas = '0.00'; $base = '0.00'; $descuentos = '0.00';
            foreach ($pasajes as $pasaje) {
                if (bccomp($pasaje->precio_base, '0', 2)<0 || bccomp($pasaje->descuento,'0',2)<0 || bccomp($pasaje->descuento,$pasaje->precio_base,2)>0) throw ValidationException::withMessages(['pasajes'=>'Importes del pasaje inválidos.']);
                $pasaje->precio_final = bcsub($pasaje->precio_base, $pasaje->descuento, 2);
                if ($pasaje->tipo_servicio !== null) {
                    if ($pasaje->base_tasa_servicio === null || bccomp($pasaje->base_tasa_servicio, $pasaje->precio_final, 2) !== 0) throw ValidationException::withMessages(['pasajes'=>'El precio cambió después de calcular la tasa. Genera una nueva cotización antes de cobrar.']);
                } else {
                    $tasa = TasaServicio::paraPrecio($pasaje->precio_final);
                    $pasaje->tasa_servicio_id = $tasa->id;
                    $pasaje->tipo_servicio = $tasa->tipo_servicio;
                    $pasaje->valor_servicio = $tasa->cantidad;
                    $pasaje->base_tasa_servicio = $pasaje->precio_final;
                    $pasaje->tasa_servicio = $tasa->calcular($pasaje->precio_final);
                }
                $pasaje->save();
                $tasas = bcadd($tasas, $pasaje->tasa_servicio, 2);
                $base = bcadd($base, $pasaje->precio_base, 2);
                $descuentos = bcadd($descuentos, $pasaje->descuento, 2);
            }
            $reserva->update(['monto_pasajes'=>$base, 'descuento_aplicado'=>$descuentos, 'tasa_servicio'=>$tasas, 'monto_total'=>bcadd(bcsub($base,$descuentos,2),$tasas,2)]);
            return $reserva;
        });
    }
}
