<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reserva {{ $reserva->codigo_referencia }}</title>
</head>
<body style="font-family: DejaVu Sans, Arial, sans-serif; font-size: 12px; color: #243746; background: #ffffff; margin: 24px;">
    <h1 style="color: #075985; font-size: 24px;">Reserva confirmada</h1>
    <p>Hola {{ $reserva->nombre_comprador }}, tu reserva está pagada. Conserva este recibo y presenta el QR de cada pasaje al abordar.</p>
    <h2 style="font-size: 18px;">{{ $reserva->codigo_referencia }}</h2>
    <p><strong>Empresa:</strong> {{ $reserva->programacion->viaje->empresa->nombre }}</p>
    <p><strong>Ruta:</strong> {{ $reserva->origenTerminal->nombre }} → {{ $reserva->destinoTerminal->nombre }}</p>
    <p><strong>Salida:</strong> {{ $reserva->tramoPrecio->getSalida()?->format('d/m/Y H:i') }}<br>
        <strong>Llegada:</strong> {{ $reserva->tramoPrecio->getLlegada()?->format('d/m/Y H:i') }}</p>
    <p><strong>Transporte:</strong> {{ $reserva->programacion->transporte->modelo }}@if ($reserva->programacion->transporte->placa) / {{ $reserva->programacion->transporte->placa }}@endif</p>
    <h2 style="font-size: 18px;">Resumen de compra</h2>
    <table style="width: 100%; border-collapse: collapse; margin-bottom: 24px;">
        @foreach (['Pasajes' => $reserva->monto_pasajes, 'Descuento' => $reserva->descuento_aplicado, 'Subtotal' => $reserva->getMontoSinTasa(), 'Tasa de servicio' => $reserva->tasa_servicio, 'Total pagado' => $reserva->monto_total] as $concepto => $monto)
            <tr>
                <td style="padding: 8px; border-bottom: 1px solid #dbe4eb;">{{ $concepto }}</td>
                <td style="padding: 8px; border-bottom: 1px solid #dbe4eb; text-align: right;">USD {{ number_format((float) $monto, 2, ',', '.') }} / Bs {{ number_format((float) $reserva->calcularMontoBs($monto), 2, ',', '.') }}</td>
            </tr>
        @endforeach
    </table>
    <h2 style="font-size: 18px;">Pasajes</h2>
    @foreach ($reserva->pasajes as $pasaje)
        <table style="width: 100%; border: 1px solid #dbe4eb; margin-bottom: 16px; page-break-inside: avoid;">
            <tr>
                <td style="padding: 16px; vertical-align: top;">
                    <strong>{{ $pasaje->viajero_nombre_completo }}</strong><br>
                    Documento: {{ $pasaje->viajero_documento ?: 'No registrado' }}<br>
                    Asiento: {{ $pasaje->numero_asiento ?? 'Sin asiento' }}<br>
                    Pasaje: USD {{ number_format((float) $pasaje->subtotal, 2, ',', '.') }} / Bs {{ number_format((float) $reserva->calcularMontoBs($pasaje->subtotal), 2, ',', '.') }}<br>
                    Localizador: {{ $pasaje->localizador }}<br>
                    {{ $reserva->origenTerminal->nombre }} → {{ $reserva->destinoTerminal->nombre }}<br>
                    Salida: {{ $reserva->tramoPrecio->getSalida()?->format('d/m/Y H:i') }}
                </td>
                <td style="padding: 12px; width: 140px; text-align: center;">
                    <img src="{{ 'data:image/png;base64,'.base64_encode($qrs[$pasaje->id]) }}" width="128" height="128" alt="QR del pasaje {{ $pasaje->id }}">
                </td>
            </tr>
        </table>
    @endforeach
    <p style="color: #64748b;">Los importes en bolívares corresponden al tipo de cambio registrado en esta reserva. El PDF adjunto contiene el recibo y todos los pasajes.</p>
</body>
</html>
