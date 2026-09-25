<?php

namespace App\Jobs;

use App\Models\OrdenCobro;
use App\Notifications\OrdenCobroNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Notification;

class NotificarOrdenCobroEmitidaJob implements ShouldQueue
{
    use Queueable;

    public function __construct(public int $ordenCobroId) {}

    public function handle(): void
    {
        $orden = OrdenCobro::query()->with('empresa')->find($this->ordenCobroId);

        if (! $orden || $orden->notificacion_emitida_at) {
            return;
        }
        info('intento de envio');
        Notification::route('mail', $orden->empresa->email)
            ->notify(new OrdenCobroNotification($orden->id, 'emitida'));

        $orden->update(['notificacion_emitida_at' => now()]);
    }
}
