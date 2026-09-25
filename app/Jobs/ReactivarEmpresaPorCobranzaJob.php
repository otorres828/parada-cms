<?php

namespace App\Jobs;

use App\Models\Empresa;
use App\Notifications\OrdenCobroNotification;
use App\Services\OrdenCobroService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Notification;

class ReactivarEmpresaPorCobranzaJob implements ShouldQueue
{
    use Queueable;

    public function __construct(public int $empresaId) {}

    public function handle(OrdenCobroService $service): void
    {
        $empresa = Empresa::find($this->empresaId);

        if (! $empresa || ! $service->reactivarSiCorresponde($empresa)) {
            return;
        }

        $orden = $empresa->ordenesCobro()->latest('fecha_aprobacion')->first();
        Notification::route('mail', $empresa->email)
            ->notify(new OrdenCobroNotification($orden->id, 'reactivacion'));
    }
}
