<?php

namespace App\Console\Commands;

use App\Models\Reserva;
use App\Services\CuponService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CancelarReservasExpiradas extends Command
{
    protected $signature = 'reservas:cancelar-expiradas';

    protected $description = 'Cancela las reservas nuevas vencidas y libera sus cupones';

    public function handle(CuponService $cuponService): int
    {
        $canceladas = 0;

        Reserva::where('estado_pago', Reserva::ESTADO_PAGO_NUEVO)
            ->where('fecha_expiracion', '<=', now())
            ->select('id')
            ->chunkById(100, function ($reservas) use ($cuponService, &$canceladas) {
                foreach ($reservas as $referencia) {
                    DB::transaction(function () use ($referencia, $cuponService, &$canceladas) {
                        $reserva = Reserva::whereKey($referencia->id)->lockForUpdate()->first();
                        if (! $reserva || $reserva->estado_pago !== Reserva::ESTADO_PAGO_NUEVO || ! $reserva->fecha_expiracion?->isPast()) {
                            return;
                        }

                        $reserva->update(['estado_pago' => Reserva::ESTADO_PAGO_CANCELADO]);
                        $cuponService->cancelarYLiberarCupon($reserva);
                        $canceladas++;
                    }, 3);
                }
            });

        $this->info("Reservas canceladas: {$canceladas}");

        return self::SUCCESS;
    }
}
