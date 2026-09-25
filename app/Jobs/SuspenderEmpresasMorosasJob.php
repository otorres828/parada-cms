<?php

namespace App\Jobs;

use App\Models\Empresa;
use App\Models\OrdenCobro;
use App\Notifications\OrdenCobroNotification;
use App\Services\OrdenCobroService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Notification;

class SuspenderEmpresasMorosasJob implements ShouldQueue
{
    use Queueable;

    public function handle(OrdenCobroService $service): void
    {
        Empresa::query()
            ->whereNull('bloqueada_por_cobranza_at')
            ->whereHas('ordenesCobro', function ($query) {
                $query->where('fecha_vencimiento', '<', now());
                $query->where(function ($query) {
                    $query->whereIn('estatus', [OrdenCobro::ESTATUS_EMITIDO, OrdenCobro::ESTATUS_RECHAZADO]);
                    $query->orWhere(function ($query) {
                        $query->where('estatus', OrdenCobro::ESTATUS_PENDIENTE);
                        $query->whereColumn('fecha_pago_reportado', '>', 'fecha_vencimiento');
                    });
                });
            })
            ->chunkById(100, function ($empresas) use ($service) {
                foreach ($empresas as $empresa) {
                    if (! $service->suspenderSiCorresponde($empresa)) {
                        continue;
                    }

                    $orden = $empresa->ordenesCobro()->latest('fecha_vencimiento')->first();
                    Notification::route('mail', $empresa->email)
                        ->notify(new OrdenCobroNotification($orden->id, 'suspension'));
                }
            });
    }
}
