{{--
    TABLA DE CUPONES DE CAMPAÑA | Presenta códigos, clientes, reservas y redenciones.
--}}

@props(['cupones', 'canViewReservation', 'sortColumn', 'sortDirection', 'routeReservation' => 'admin.reservas.detail'])

<x-list.table>

    <thead>

        <tr>
            <th>Código
                <x-list.sortable-button column="codigo" :$sortColumn :$sortDirection />
            </th>

            <th>Estado</th>

            <th>Cliente</th>

            <th class="text-nowrap">Reserva</th>

            <th>Fecha de redención</th>

        </tr>
    </thead>

    <tbody>

        @forelse ($cupones as $cupon)
            <tr wire:key="cupon-{{ $cupon->id }}">
                <td>
                    {{ $cupon->codigo }}
                </td>

                <td>
                    {{ $cupon->redimido ? 'Redimido' : 'Disponible' }}
                </td>

                <td>
                    {{ $cupon->usuario?->name ?? '—' }}
                </td>

                <td class="text-nowrap">
                    @if ($cupon->reserva)
                        @if ($canViewReservation)
                            <a href="{{ route($routeReservation, $cupon->reserva->id) }}"
                                wire:navigate>
                                {{ $cupon->reserva->codigo_referencia }}
                            </a>
                        @else
                            {{ $cupon->reserva->codigo_referencia }}
                        @endif
                    @else
                        —
                    @endif
                </td>

                <td>
                    {{ $cupon->fecha_redencion?->format('d/m/Y H:i') ?? '—' }}
                </td>

            </tr>

        @empty

            <tr>
                <td colspan="5" class="text-center py-4">
                    No se encontraron cupones.
                </td>

            </tr>
        @endforelse

    </tbody>

</x-list.table>


