<?php

namespace App\Notifications;

use App\Exports\PasajesExport;
use App\Exports\ReservasExport;
use App\Models\OrdenCobro;
use App\Models\Pasaje;
use App\Models\Reserva;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Maatwebsite\Excel\Excel as ExcelFormat;
use Maatwebsite\Excel\Facades\Excel;

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

        $mensaje = (new MailMessage)
            ->subject($this->asunto($orden))
            ->greeting('Hola, '.$orden->empresa->nombre)
            ->line($this->mensaje($orden))
            ->line('Período: '.$orden->periodo_desde->format('d/m/Y').' al '.$orden->periodo_hasta->format('d/m/Y'))
            ->line('Total: '.$orden->total)
            ->line('Vencimiento: '.$orden->fecha_vencimiento->format('d/m/Y H:i'));

        if ($this->tipo !== 'emitida') {
            return $mensaje;
        }

        $reservaIds = collect($orden->reservas_incluidas)
            ->pluck('reserva_id')
            ->filter()
            ->map(function ($reservaId) {
                return (int) $reservaId;
            })
            ->values()
            ->all();

        if ($reservaIds === []) {
            return $mensaje;
        }

        $consultaReservas = Reserva::searchAdmin()
            ->with(['cupon', 'reservaOriginal'])
            ->withCount('pasajes')
            ->whereKey($reservaIds)
            ->orderBy('reservas.id');

        $consultaPasajes = Pasaje::searchAdmin()
            ->whereIn('pasajes.reserva_id', $reservaIds)
            ->orderBy('pasajes.id');

        $mensaje->attachData(
            Excel::raw(
                new ReservasExport($consultaReservas, ['empresa']),
                ExcelFormat::XLSX,
            ),
            'reservas-'.$orden->codigo.'.xlsx',
            ['mime' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
        );

        $mensaje->attachData(
            Excel::raw(
                new PasajesExport($consultaPasajes),
                ExcelFormat::XLSX,
            ),
            'pasajes-'.$orden->codigo.'.xlsx',
            ['mime' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
        );

        return $mensaje;
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
            'motivo' => $exception->getMessage(),
        ]);

        // Ejemplo: Marcar en base de datos que hubo un error
        // $orden = OrdenCobro::find($this->ordenCobroId);
        // $orden->update(['estado_notificacion' => 'error']);
    }
}
