<?php

namespace App\Jobs;

use App\Models\Empresa;
use App\Services\OrdenCobroService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class GenerarOrdenesCobroJob implements ShouldQueue
{
    use Queueable;

    public function handle(OrdenCobroService $service): void
    {
        Empresa::query()
            ->where('tipo_contrato', Empresa::CONTRATO_ELLOS_RECIBEN)
            ->where('dia_corte', now()->dayOfWeekIso)
            ->where('hora_corte', '<=', now()->format('H:i:s'))
            ->where('estatus', Empresa::ESTADO_ACTIVE)
            ->chunkById(100, function ($empresas) use ($service) {
                foreach ($empresas as $empresa) {
                    $service->generar($empresa);
                }
            });
    }
}
