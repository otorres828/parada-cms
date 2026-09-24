{{--
    TABLA DE RESERVAS DEL CLIENTE | Presenta compras pagadas y pendientes con búsqueda local.
--}}

@props(['reservas', 'canReservasDetail'])

<table class="table align-middle mb-0">

    <thead>

        <tr>
            <th>Código</th>

            <th>Empresa</th>

            <th>Fecha de compra</th>

            <th>Estado</th>

            <th>Total</th>

        </tr>
    </thead>

    <tbody>

        @forelse($reservas as $reserva)
            <tr data-search="{{ $reserva->codigo_referencia }} {{ $reserva->programacion?->viaje?->empresa?->nombre }} {{ $reserva->getStatusPago() }}"
                x-show="matches($el.dataset.search)">
                <td>

                    @if ($canReservasDetail)
                        <a href="{{ route('admin.reservas.detail', $reserva->id) }}"
                            wire:navigate>{{ $reserva->codigo_referencia }}</a>
                    @else
                        {{ $reserva->codigo_referencia }}
                    @endif

                </td>

                <td>
                    {{ $reserva->programacion?->viaje?->empresa?->nombre }}
                </td>

                <td>
                    {{ $reserva->fecha_compra?->format('d/m/Y H:i') }}
                </td>

                <td>
                    {{ ucfirst($reserva->getStatusPago()) }}
                </td>

                <td>
                    {{ number_format($reserva->monto_total, 2) }}
                </td>

            </tr>

        @empty

            <tr>
                <td colspan="5" class="text-center py-4">
                    No hay reservas pagadas o pendientes.
                </td>

            </tr>
        @endforelse

        @if ($reservas->isNotEmpty())
            <tr x-cloak x-show="search && !hasMatches()">
                <td colspan="5" class="text-center py-4">
                    No hay coincidencias.
                </td>

            </tr>
        @endif

    </tbody>
</table>


