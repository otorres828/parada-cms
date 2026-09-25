<?php

namespace App\Jobs;

use App\Models\OrdenCobro;
use App\Notifications\OrdenCobroNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Notification;

class RecordarVencimientoOrdenCobroJob implements ShouldQueue
{
    use Queueable;

    public function handle(): void
    {
        OrdenCobro::query()
            ->with('empresa')
            ->where('estatus', OrdenCobro::ESTATUS_EMITIDO)
            ->whereBetween('fecha_vencimiento', [now(), now()->addHours(48)])
            ->chunkById(100, function ($ordenes) {
                foreach ($ordenes as $orden) {
                    $horas = now()->diffInHours($orden->fecha_vencimiento, false);
                    $campo = $horas <= 24 ? 'recordatorio_24_at' : 'recordatorio_48_at';

                    if ($orden->{$campo}) {
                        continue;
                    }

                    Notification::route('mail', $orden->empresa->email)
                        ->notify(new OrdenCobroNotification($orden->id, 'recordatorio'));

                    $orden->update([$campo => now()]);
                }
            });
    }
}
