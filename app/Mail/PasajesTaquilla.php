<?php

namespace App\Mail;

use App\Models\Reserva;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class PasajesTaquilla extends Mailable
{
    public function __construct(public Reserva $reserva) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Pasajes de la reserva '.$this->reserva->codigo_referencia);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.pasajes-taquilla');
    }

    public function attachments(): array
    {
        $adjuntos = [];
        foreach ($this->reserva->pasajes as $pasaje) {
            $qr = $pasaje->getQr();
            if ($qr !== null) {
                $svg = base64_decode(explode(',', $qr, 2)[1]);
                $adjuntos[] = Attachment::fromData(function () use ($svg) {
                    return $svg;
                }, 'pasaje-'.$pasaje->id.'.svg')->withMime('image/svg+xml');
            }
        }

        return $adjuntos;
    }
}
