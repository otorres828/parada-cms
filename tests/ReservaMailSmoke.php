<?php

// Las fixtures usan SQLite en memoria; no se envían correos reales.
require __DIR__.'/TaquillaSmoke.php';

use App\Models\Reserva;
use App\Notifications\ReservaPagadaNotification;
use App\Services\PagoReservaService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

Notification::fake();
$reservaMail = $efectivo->fresh();
$reservaMail->sendMailReserva();
$check(Notification::sent(new Illuminate\Notifications\AnonymousNotifiable, ReservaPagadaNotification::class, function ($notification, $channels, $notifiable) use ($reservaMail) {
    return $notification->reservaId === $reservaMail->id
        && $notification->afterCommit === true
        && $notifiable->routes['mail'] === 'comprador@example.test';
})->count() === 1);

Notification::fake();
PagoReservaService::confirmarPago($reservaMail->id, $reservaMail->monto_total);
$check(Notification::sentNotifications() === []);
$reservaMail->update(['estado_pago' => Reserva::ESTADO_PAGO_PENDIENTE]);
PagoReservaService::confirmarPago($reservaMail->id, $reservaMail->monto_total);
$check(Notification::sent(new Illuminate\Notifications\AnonymousNotifiable, ReservaPagadaNotification::class)->count() === 1);
Notification::fake();
$reservaMail->refresh();
$reservaMail->save();
$reservaMail->update(['fecha_pago' => now()->addMinute()]);
$check(Notification::sentNotifications() === []);
$reservaMail->estado_pago = Reserva::ESTADO_PAGO_PENDIENTE;
$reservaMail->sendMailReserva();
$check(Notification::sentNotifications() === []);
$reservaMail->estado_pago = Reserva::ESTADO_PAGO_PAGADO;
$reservaMail->comprador_json = ['nombre' => 'Sin correo'];
$reservaMail->sendMailReserva();
$check(Notification::sentNotifications() === []);
$reservaMail->usuario_id = $cliente->id;
$reservaMail->sendMailReserva();
$check(Notification::sent(new Illuminate\Notifications\AnonymousNotifiable, ReservaPagadaNotification::class, fn ($notification, $channels, $notifiable) => $notifiable->routes['mail'] === $cliente->email)->count() === 1);

$notification = new ReservaPagadaNotification($efectivo->id);
$check($notification->shouldSend(new stdClass, 'mail'));
$mail = $notification->toMail(new stdClass);
$check(count($mail->rawAttachments) === 1);
$pdf = $mail->rawAttachments[0]['data'];
$check(str_starts_with($pdf, '%PDF-'));
$message = new Illuminate\Mail\Message(new Symfony\Component\Mime\Email);
$html = (string) app(Illuminate\Mail\Markdown::class)->render($mail->markdown, $mail->viewData + ['message' => $message]);
$check(str_contains($html, $efectivo->codigo_referencia));
$check(str_contains($html, 'Bs'));
$check(str_contains($html, 'width: 570px'));
$check(! str_contains($html, '<pre>'));
$text = (string) app(Illuminate\Mail\Markdown::class)->renderText($mail->markdown, $mail->viewData + ['message' => $message]);
$check(str_contains($text, $efectivo->codigo_referencia));
$check(str_contains($text, $efectivo->pasajes()->first()->localizador));
file_put_contents(storage_path('app/reserva-mail-preview.html'), $html);
$check(count($message->getSymfonyMessage()->getAttachments()) === $efectivo->pasajes()->count());
file_put_contents(storage_path('app/reserva-mail-preview.pdf'), $pdf);
file_put_contents(storage_path('app/reserva-mail-qr.png'), $mail->viewData['qrs'][$efectivo->pasajes()->first()->id]);
file_put_contents(storage_path('app/reserva-mail-localizador.txt'), $efectivo->pasajes()->first()->localizador);

// Verifica que el recibo admite varios pasajeros y conserva todos sus QR.
$grupo = $efectivo->replicate();
$grupo->codigo_referencia = 'TQ-GRUPO';
$grupo->monto_pasajes = '80.00';
$grupo->monto_total = '80.00';
$grupo->save();
for ($indice = 1; $indice <= 8; $indice++) {
    $pasajeGrupo = $efectivo->pasajes()->first()->replicate();
    $pasajeGrupo->reserva_id = $grupo->id;
    $pasajeGrupo->numero_asiento = $indice;
    $pasajeGrupo->localizador = Reserva::generarLocalizador(7);
    $pasajeGrupo->save();
}
$mailGrupo = (new ReservaPagadaNotification($grupo->id))->toMail(new stdClass);
$check(count($mailGrupo->viewData['qrs']) === 8);
$check(str_starts_with($mailGrupo->rawAttachments[0]['data'], '%PDF-'));
file_put_contents(storage_path('app/reserva-mail-grupo.pdf'), $mailGrupo->rawAttachments[0]['data']);

$efectivo->update(['estado_pago' => Reserva::ESTADO_PAGO_REPROGRAMADO]);
$check(! $notification->shouldSend(new stdClass, 'mail'));
$efectivo->update(['estado_pago' => Reserva::ESTADO_PAGO_PAGADO]);

// Una reserva creada pagada también dispara el evento, una sola vez.
Notification::fake();
$nuevaPagada = $efectivo->replicate();
$nuevaPagada->codigo_referencia = 'TQ-BOOTED';
$nuevaPagada->save();
$nuevaPagada->save();
$nuevaPagada->update(['fecha_pago' => now()->addMinute()]);
$check(Notification::sent(new Illuminate\Notifications\AnonymousNotifiable, ReservaPagadaNotification::class)->count() === 1);

// Reenvío desde el detalle: permiso, correo, estado e aislamiento empresarial.
Notification::fake();
Illuminate\Support\Facades\Auth::guard('empresa')->login($usuarioEmpresa);
$detalle = Livewire\Livewire::test(App\Livewire\Empresas\Reservas\DetailReserva::class, ['reserva_id' => $efectivo->id]);
$detalle->call('reenviarCorreo');
$check(in_array('empresas_reserva_success', array_column($detalle->effects['dispatches'] ?? [], 'name'), true));
$check(Notification::sent(new Illuminate\Notifications\AnonymousNotifiable, ReservaPagadaNotification::class)->count() === 1);
Notification::fake();
$efectivo->update(['estado_pago' => Reserva::ESTADO_PAGO_PENDIENTE]);
$detalle->call('reenviarCorreo');
$check(in_array('empresas_reserva_error', array_column($detalle->effects['dispatches'] ?? [], 'name'), true));
$check(Notification::sentNotifications() === []);
$efectivo->update(['estado_pago' => Reserva::ESTADO_PAGO_PAGADO, 'comprador_json' => ['nombre' => 'Sin correo']]);
Notification::fake();
$detalle->call('reenviarCorreo');
$check(in_array('empresas_reserva_error', array_column($detalle->effects['dispatches'] ?? [], 'name'), true));
$check(Notification::sentNotifications() === []);
$efectivo->update(['comprador_json' => ['nombre' => 'Comprador', 'email' => 'comprador@example.test']]);

// La cola real almacena el trabajo únicamente después del commit.
$efectivo->update(['estado_pago' => Reserva::ESTADO_PAGO_PENDIENTE]);
Notification::swap(new Illuminate\Notifications\ChannelManager($app));
config(['queue.default' => 'database', 'queue.connections.database.connection' => 'sqlite']);
$jobsAntes = DB::table('jobs')->count();
DB::beginTransaction();
$efectivo->fresh()->update(['estado_pago' => Reserva::ESTADO_PAGO_PAGADO]);
$check(DB::table('jobs')->count() === $jobsAntes);
DB::rollBack();
$check(DB::table('jobs')->count() === $jobsAntes);
DB::transaction(function () use ($efectivo) {
    $reserva = $efectivo->fresh();
    $reserva->update(['estado_pago' => Reserva::ESTADO_PAGO_PAGADO]);
    $reserva->save();
});
$check(DB::table('jobs')->count() === $jobsAntes + 1);

echo "Reserva mail OK: destinatarios, estado, idempotencia, PDF, QR y cola después del commit.\n";
