@props(['reserva', 'qrs', 'message'])

<table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="color: #243746; font-size: 14px;">
    <tr>
        <td style="padding-bottom: 12px;">
            <span style="background: #e7f5ed; color: #187044; font-size: 12px; font-weight: 700; padding: 6px 12px; border-radius: 20px;">PAGO CONFIRMADO</span>
        </td>
    </tr>
    <tr>
        <td>
            <h1 style="font-size: 26px; line-height: 1.25; color: #0c3853; margin: 4px 0 12px;">Tu próximo viaje está listo</h1>
            <p style="font-size: 15px; line-height: 1.6; color: #617183; margin: 0 0 24px;">Hola {{ $reserva->nombre_comprador }}, tu reserva está confirmada. Aquí tienes el comprobante y tus pasajes.</p>
        </td>
    </tr>
    <tr>
        <td style="background: #0c3853; border-radius: 10px; padding: 20px 24px;">
            <p style="font-size: 11px; letter-spacing: 1px; color: #bed5e4; margin: 0 0 7px;">CÓDIGO DE RESERVA</p>
            <p style="font-size: 24px; font-weight: 700; color: #ffffff; margin: 0 0 8px;">{{ $reserva->codigo_referencia }}</p>
            <p style="font-size: 13px; color: #e2edf4; margin: 0;">{{ $reserva->programacion->viaje->empresa->nombre }} · {{ $reserva->pasajes->count() }} {{ $reserva->pasajes->count() === 1 ? 'pasajero' : 'pasajeros' }}</p>
        </td>
    </tr>
    <tr>
        <td style="padding: 24px 0 12px; font-size: 18px; font-weight: 700; color: #0c3853;">Tu viaje</td>
    </tr>
    <tr>
        <td style="border: 1px solid #e0e7ed; border-radius: 10px; padding: 20px;">
            <p style="color: #e86126; font-size: 11px; font-weight: 700; letter-spacing: 1px; margin: 0 0 8px;">EMBARQUE</p>
            <p style="font-size: 17px; font-weight: 700; color: #0c3853; margin: 0 0 6px;">{{ $reserva->tramoPrecio->getSalida()?->format('d/m/Y · H:i') }}</p>
            <p style="font-size: 14px; color: #243746; margin: 0 0 4px;">{{ $reserva->origenTerminal->nombre }}</p>
            @if ($reserva->origenTerminal->direccion)
                <p style="font-size: 12px; color: #617183; margin: 0;">{{ $reserva->origenTerminal->direccion }}</p>
            @endif
            <hr style="border: 0; border-top: 1px solid #e0e7ed; margin: 18px 0;">
            <p style="color: #e86126; font-size: 11px; font-weight: 700; letter-spacing: 1px; margin: 0 0 8px;">DESEMBARQUE</p>
            <p style="font-size: 17px; font-weight: 700; color: #0c3853; margin: 0 0 6px;">{{ $reserva->tramoPrecio->getLlegada()?->format('d/m/Y · H:i') }}</p>
            <p style="font-size: 14px; color: #243746; margin: 0 0 4px;">{{ $reserva->destinoTerminal->nombre }}</p>
            @if ($reserva->destinoTerminal->direccion)
                <p style="font-size: 12px; color: #617183; margin: 0;">{{ $reserva->destinoTerminal->direccion }}</p>
            @endif
        </td>
    </tr>
    <tr>
        <td style="padding: 12px 4px 0; font-size: 12px; color: #617183;">Transporte: {{ $reserva->programacion->transporte->modelo }}@if ($reserva->programacion->transporte->placa) · {{ $reserva->programacion->transporte->placa }}@endif</td>
    </tr>
    <tr>
        <td style="padding: 24px 0 12px; font-size: 18px; font-weight: 700; color: #0c3853;">Resumen de compra</td>
    </tr>
    <tr>
        <td>
            <table width="100%" cellpadding="0" cellspacing="0" role="presentation">
                @foreach (['Pasajes' => $reserva->monto_pasajes, 'Descuento' => $reserva->descuento_aplicado, 'Subtotal' => $reserva->getMontoSinTasa(), 'Tasa de servicio' => $reserva->tasa_servicio] as $concepto => $monto)
                    <tr>
                        <td style="padding: 9px 0; border-bottom: 1px solid #edf1f5; font-size: 13px; color: #617183;">{{ $concepto }}</td>
                        <td style="padding: 9px 0; border-bottom: 1px solid #edf1f5; font-size: 13px; text-align: right; color: #243746;">USD {{ number_format((float) $monto, 2, ',', '.') }} / Bs {{ number_format((float) $reserva->calcularMontoBs($monto), 2, ',', '.') }}</td>
                    </tr>
                @endforeach
                <tr>
                    <td colspan="2" style="padding: 16px 18px; background: #edf5fa; border-radius: 8px;">
                        <p style="font-size: 12px; color: #47677d; margin: 0 0 5px;">TOTAL PAGADO</p>
                        <p style="font-size: 20px; font-weight: 700; color: #0c3853; margin: 0;">USD {{ number_format((float) $reserva->monto_total, 2, ',', '.') }} / Bs {{ number_format((float) $reserva->calcularMontoBs($reserva->monto_total), 2, ',', '.') }}</p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <td style="padding: 24px 0 8px; font-size: 18px; font-weight: 700; color: #0c3853;">Tus pasajes</td>
    </tr>
    <tr>
        <td style="padding-bottom: 16px; font-size: 13px; line-height: 1.5; color: #617183;">Presenta el QR de cada pasajero al abordar. Puedes guardar o imprimir el PDF adjunto.</td>
    </tr>
    @foreach ($reserva->pasajes as $pasaje)
        <tr>
            <td style="padding-bottom: 16px;">
                <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="border: 1px solid #dbe5ed; border-top: 4px solid #e86126; border-radius: 10px;">
                    <tr>
                        <td style="padding: 20px 20px 12px;">
                            <p style="font-size: 17px; font-weight: 700; color: #0c3853; margin: 0 0 10px;">{{ $pasaje->viajero_nombre_completo }}</p>
                            <p style="font-size: 13px; line-height: 1.8; color: #617183; margin: 0;">Documento: <strong style="color: #243746;">{{ $pasaje->viajero_documento ?: 'No registrado' }}</strong><br>Asiento: <strong style="color: #243746;">{{ $pasaje->numero_asiento ?? 'Sin asiento' }}</strong><br>Localizador: <strong style="color: #243746;">{{ $pasaje->localizador }}</strong><br>Pasaje: USD {{ number_format((float) $pasaje->subtotal, 2, ',', '.') }} / Bs {{ number_format((float) $reserva->calcularMontoBs($pasaje->subtotal), 2, ',', '.') }}</p>
                        </td>
                    </tr>
                    <tr>
                        <td align="center" style="padding: 0 20px 20px;">
                            <x-mail::reserva-qr :qr="$qrs[$pasaje->id]" :pasaje="$pasaje" :message="$message" />
                            <p style="font-size: 11px; color: #617183; margin: 10px 0 0;">QR de embarque · {{ $pasaje->localizador }}</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    @endforeach
    <tr>
        <td style="padding: 16px 0 0; border-top: 1px solid #e0e7ed; font-size: 12px; line-height: 1.6; color: #788798;">Los importes en bolívares corresponden al tipo de cambio registrado en esta reserva. El PDF adjunto incluye el comprobante completo y los QR de todos los pasajeros.</td>
    </tr>
</table>
