{{--
    TABLA DE PRECIOS POR TRAMO | Renderiza los precios base de las combinaciones de la ruta.
--}}

@props(['tramoPrecios', 'tipoCambio' => null])

<table class="table table-sm align-middle mb-0">
    <thead>
        <tr>
            <th>Tramo Comercial</th>
            <th class="text-end">Precio base</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($tramoPrecios as $tp)
            <tr>
                <td>
                    <span class="fw-semibold">{{ $tp->origenTerminal?->nombre }}</span>
                    <i class="bi bi-arrow-right text-muted mx-1"></i>
                    <span
                        class="fw-semibold">{{ $tp->destinoTerminal?->nombre }}</span>
                </td>
                <td class="text-end text-success fw-bold">
                    @if ($tp->precio !== null)
                        <x-money.dual :usd="$tp->precio" :bs="\App\Support\ConversorMoneda::aBolivares($tp->precio, $tipoCambio)" />
                    @else
                        <span class="text-muted fw-normal">Sin configurar</span>
                    @endif
                </td>
            </tr>
        @endforeach
    </tbody>
</table>



