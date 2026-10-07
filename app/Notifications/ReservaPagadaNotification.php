<?php

namespace App\Notifications;

use App\Models\Reserva;
use BaconQrCode\Renderer\GDLibRenderer;
use BaconQrCode\Writer;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReservaPagadaNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(public int $reservaId)
    {
        $this->afterCommit();
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function shouldSend(object $notifiable, string $channel): bool
    {
        return Reserva::whereKey($this->reservaId)->where('estado_pago', Reserva::ESTADO_PAGO_PAGADO)->exists();
    }

    public function toMail(object $notifiable): MailMessage
    {
        $reserva = Reserva::findOrFail($this->reservaId)->detalle()->load([
            'usuario',
            'programacion.transporte',
            'programacion.viaje.empresa',
        ]);
        $writer = new Writer(new GDLibRenderer(320, 4));
        $qrs = [];

        foreach ($reserva->pasajes as $pasaje) {
            $qrs[$pasaje->id] = $writer->writeString($pasaje->localizador);
        }

        $options = new Options;
        $options->set('isRemoteEnabled', false);
        $options->set('isPhpEnabled', false);
        $options->set('defaultFont', 'DejaVu Sans');
        $pdf = new Dompdf($options);
        $pdf->loadHtml(view('emails.reserva-pagada-pdf', compact('reserva', 'qrs'))->render());
        $pdf->setPaper('A4');
        $pdf->render();

        return (new MailMessage)
            ->subject('Reserva confirmada '.$reserva->codigo_referencia)
            ->markdown('emails.reserva-pagada', compact('reserva', 'qrs'))
            ->attachData($pdf->output(), 'reserva-'.$reserva->codigo_referencia.'.pdf', ['mime' => 'application/pdf']);
    }

    public function failed(\Throwable $exception): void
    {
        report($exception);
    }
}
