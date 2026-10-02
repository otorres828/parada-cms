{{-- Confirmación de venta por taquilla y QR adjuntos. --}}
<h1>Tu reserva {{ $reserva->codigo_referencia }}</h1>
<p>Hola, {{ $reserva->nombre_comprador }}.</p>
<p>Origen: {{ $reserva->origenTerminal?->nombre }}. Destino: {{ $reserva->destinoTerminal?->nombre }}.</p>
<p>Salida del tramo: {{ $reserva->tramoPrecio?->getSalida()?->format('d/m/Y H:i') ?? 'Sin horario' }}. Llegada:
    {{ $reserva->tramoPrecio?->getLlegada()?->format('d/m/Y H:i') ?? 'Sin horario' }}.</p>
<p>Adjuntamos el código QR de cada pasaje. Preséntalo al abordar.</p>
<ul>
    @foreach ($reserva->pasajes as $pasaje)
        <li>Pasaje #{{ $pasaje->id }}: {{ $pasaje->viajero_nombre_completo }} · Asiento {{ $pasaje->numero_asiento }}
            · Localizador {{ $pasaje->localizador }}</li>
    @endforeach
</ul>
