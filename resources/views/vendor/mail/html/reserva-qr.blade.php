@props(['qr', 'pasaje', 'message'])

<img src="{{ $message->embedData($qr, 'pasaje-'.$pasaje->id.'.png', 'image/png') }}" width="160" height="160" alt="QR del pasaje {{ $pasaje->localizador }}" style="display: block; margin: 16px auto 0;">
