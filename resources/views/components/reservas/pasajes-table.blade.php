{{--
    TABLA DE PASAJES DE LA RESERVA | Presenta viajeros, precios, estado de abordaje y QR.
--}}

@props(['tickets', 'canViewTicket'])

<table class="table align-middle">

    <thead>

        <tr>
            <th>Pasaje</th>

            <th>Nombre</th>

            <th>Documento</th>

            <th>Fecha de nacimiento</th>

            <th>Abordaje</th>

            <th class="text-end">Precio</th>

            <th class="text-end">Descuento</th>

            <th class="text-end">Subtotal</th>

            <th class="text-end">Tasa de servicio</th>

            <th class="text-end">Total</th>

            <th class="text-center">QR</th>

        </tr>
    </thead>

    <tbody>

        @forelse ($tickets as $ticket)
            <tr>
                <td>
                    @if ($canViewTicket)
                        <a href="{{ route('admin.pasajes.detail', $ticket->id) }}" wire:navigate>
                            #{{ $ticket->id }}
                        </a>
                    @else
                        #{{ $ticket->id }}
                    @endif
                </td>

                <td>
                    {{ $ticket->viajero?->nombre ?? 'Pasajero por completar' }} {{ $ticket->viajero?->apellido }}
                </td>

                <td>
                    {{ $ticket->viajero?->documento_identidad }}
                </td>

                <td>
                    {{ $ticket->viajero?->fecha_nacimiento?->format('d/m/Y') ?? '—' }}
                </td>

                <td>
                    {{ $ticket->abordado ? 'Abordado' : 'Pendiente' }}
                </td>

                <td class="text-end">
                    {{ number_format($ticket->precio_base, 2) }}
                </td>

                <td class="text-end">
                    {{ number_format($ticket->descuento, 2) }}
                </td>

                <td class="text-end">
                    {{ number_format($ticket->subtotal, 2) }}
                </td>

                <td class="text-end">
                    {{ number_format($ticket->tasa_servicio, 2) }}
                </td>

                <td class="text-end">
                    {{ number_format($ticket->total, 2) }}
                </td>

                <td class="text-center">
                    <x-list.pasaje-qr :pasaje="$ticket" />
                </td>

            </tr>

        @empty

            <tr>
                <td colspan="11">
                    No hay pasajeros.
                </td>

            </tr>
        @endforelse

    </tbody>
</table>


