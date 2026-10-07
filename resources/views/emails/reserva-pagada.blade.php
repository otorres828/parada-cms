{{-- Correo de confirmación: plantilla Markdown y componentes nativos de Laravel. --}}
<x-mail::message>
# Reserva confirmada

Hola {{ $reserva->nombre_comprador }}, tu pago fue confirmado. Estos son los detalles de tu viaje.

<x-mail::panel>
**Código de reserva: {{ $reserva->codigo_referencia }}**

**Empresa:** {{ $reserva->programacion->viaje->empresa->nombre }}<br>
**Origen:** {{ $reserva->origenTerminal->nombre }}<br>
**Destino:** {{ $reserva->destinoTerminal->nombre }}<br>
**Salida:** {{ $reserva->tramoPrecio->getSalida()?->format('d/m/Y H:i') }}<br>
**Llegada:** {{ $reserva->tramoPrecio->getLlegada()?->format('d/m/Y H:i') }}<br>
**Transporte:** {{ $reserva->programacion->transporte->modelo }}@if ($reserva->programacion->transporte->placa) / {{ $reserva->programacion->transporte->placa }}@endif
</x-mail::panel>

## Resumen de compra

<x-mail::table>
<table>
    <thead>
        <tr>
            <th>Concepto</th>
            <th style="text-align: right;">Importe</th>
        </tr>
    </thead>
    <tbody>
        @foreach (['Pasajes' => $reserva->monto_pasajes, 'Descuento' => $reserva->descuento_aplicado, 'Subtotal' => $reserva->getMontoSinTasa(), 'Tasa de servicio' => $reserva->tasa_servicio, 'Total pagado' => $reserva->monto_total] as $concepto => $monto)
            <tr>
                <td>@if ($loop->last)<strong>{{ $concepto }}</strong>@else{{ $concepto }}@endif</td>
                <td style="text-align: right;">USD {{ number_format((float) $monto, 2, ',', '.') }} / Bs {{ number_format((float) $reserva->calcularMontoBs($monto), 2, ',', '.') }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
</x-mail::table>

## Tus pasajes

Presenta el QR de cada pasajero al abordar. También encontrarás todos los pasajes en el PDF adjunto.

@foreach ($reserva->pasajes as $pasaje)
<x-mail::panel>
### {{ $pasaje->viajero_nombre_completo }}

**Documento:** {{ $pasaje->viajero_documento ?: 'No registrado' }}<br>
**Asiento:** {{ $pasaje->numero_asiento ?? 'Sin asiento' }}<br>
**Localizador:** {{ $pasaje->localizador }}<br>
**Pasaje:** USD {{ number_format((float) $pasaje->subtotal, 2, ',', '.') }} / Bs {{ number_format((float) $reserva->calcularMontoBs($pasaje->subtotal), 2, ',', '.') }}

<x-mail::reserva-qr
    :qr="$qrs[$pasaje->id]"
    :pasaje="$pasaje"
    :message="$message"
/>
</x-mail::panel>
@endforeach

<x-slot:subcopy>
Los importes en bolívares corresponden al tipo de cambio registrado en esta reserva. Conserva el PDF adjunto como recibo de tu compra.
</x-slot:subcopy>
</x-mail::message>
