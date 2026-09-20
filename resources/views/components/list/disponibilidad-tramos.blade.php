{{-- Disponibilidad calculada por origen y destino; los disponibles respetan el tope configurado. --}}
@props(['tramos' => []])

@if (count($tramos))
    <details class="small text-start">
        <summary>Ver disponibilidad por tramo ({{ count($tramos) }})</summary>

        <ul class="list-unstyled mt-2 mb-0">
            @foreach ($tramos as $tramo)
                <li class="mb-2">
                    <span>{{ $tramo['origen'] }} → {{ $tramo['destino'] }}</span>
                    <strong class="d-block">{{ $tramo['disponibles'] }} / {{ $tramo['capacidad'] }} asientos disponibles</strong>
                </li>
            @endforeach
        </ul>
    </details>
@else
    <span class="text-body-secondary">Sin tramos configurados</span>
@endif
