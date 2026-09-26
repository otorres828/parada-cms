<?php

namespace App\Console\Commands;

use App\Services\TipoCambioService;
use Illuminate\Console\Command;
use Throwable;

class ActualizarTiposCambio extends Command
{
    protected $signature = 'tipos-cambio:actualizar';

    protected $description = 'Consulta las tasas oficiales BCV y guarda un registro histórico';

    public function handle(TipoCambioService $service): int
    {
        try {
            $tipoCambio = $service->actualizar();
        } catch (Throwable $exception) {
            report($exception);
            $this->error('No fue posible actualizar las tasas de cambio: '.$exception->getMessage());

            return self::FAILURE;
        }

        $this->info(sprintf(
            'Tasa #%d registrada: 1 USD = %s Bs; 1 EUR = %s Bs.',
            $tipoCambio->id,
            $tipoCambio->valor_usd,
            $tipoCambio->valor_eur,
        ));

        return self::SUCCESS;
    }
}
