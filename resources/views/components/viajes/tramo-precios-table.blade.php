{{--
    TABLA DE PRECIOS POR TRAMO | Renderiza la matriz O&D configurada para la salida más reciente.
--}}

@props(['tramoPrecios', 'tipoCambio' => null])

<table class="table table-sm align-middle mb-0">
    <thead>
        <tr>
            <th>Tramo Comercial</th>
            <th class="text-end">Precio Configurado</th>
            <th class="text-center">Tope Asientos</th>
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
                    <x-money.dual :usd="$tp->precio" :bs="$tp->calcularMontoBs($tipoCambio)" />
                </td>
                <td class="text-center">
                    @if ($tp->asientos_maximos_permitidos)
                        <span
                            class="badge text-bg-warning">{{ $tp->asientos_maximos_permitidos }}
                            asientos</span>
                    @else
                        <span class="badge text-bg-secondary">Sin tope (Libre)</span>
                    @endif
                </td>
            </tr>
        @endforeach
    </tbody>
</table>



