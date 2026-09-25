<?php

namespace App\Notifications;

use App\Models\OrdenCobro;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class OrdenCobroNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public int $ordenCobroId, public string $tipo) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $orden = OrdenCobro::findOrFail($this->ordenCobroId);

        return (new MailMessage)
            ->subject($this->asunto($orden))
            ->greeting('Hola, '.$orden->empresa->nombre)
            ->line($this->mensaje($orden))
            ->line('Período: '.$orden->periodo_desde->format('d/m/Y').' al '.$orden->periodo_hasta->format('d/m/Y'))
            ->line('Total: '.$orden->total)
            ->line('Vencimiento: '.$orden->fecha_vencimiento->format('d/m/Y H:i'));
    }

    private function asunto(OrdenCobro $orden): string
    {
        return match ($this->tipo) {
            'recordatorio' => 'Recordatorio de pago '.$orden->codigo,
            'suspension' => 'Empresa suspendida por cobranza',
            'reactivacion' => 'Empresa reactivada',
            'rechazo' => 'Comprobante rechazado para '.$orden->codigo,
            default => 'Nueva orden de cobro '.$orden->codigo,
        };
    }

    private function mensaje(OrdenCobro $orden): string
    {
        return match ($this->tipo) {
            'recordatorio' => 'La orden de cobro está próxima a vencer.',
            'suspension' => 'La empresa fue suspendida porque mantiene una orden de cobro vencida.',
            'reactivacion' => 'La empresa fue reactivada después de regularizar sus órdenes de cobro.',
            'rechazo' => 'El comprobante de la orden fue rechazado. Consulte el detalle y registre un nuevo pago.',
            default => 'Se emitió una nueva orden de cobro por las tasas de servicio del período.',
        };
    }

    public function failed(\Throwable $exception): void
    {
        // Aquí llega la excepción. Puedes hacer lo que quieras con ella.
        
        info('Fallo el correo de la orden '.$this->ordenCobroId, [
            'motivo' => $exception->getMessage()
        ]);
        
        // Ejemplo: Marcar en base de datos que hubo un error
        // $orden = OrdenCobro::find($this->ordenCobroId);
        // $orden->update(['estado_notificacion' => 'error']);
    }
}
