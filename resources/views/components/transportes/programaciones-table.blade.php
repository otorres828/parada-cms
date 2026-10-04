{{--
    TABLA DE PROGRAMACIONES DEL TRANSPORTE | Presenta las salidas y sus resultados comerciales.
--}}

@props([
    'programaciones', 
    'canViewPassengers', 
    'tipoCambio' => null, 
    'viewTasaServicio' => true, 
    'routeProgramacion'
])

<table class="table align-middle mb-0">

    <thead>

        <tr>
            <th>Programación</th>

            <th>Salida</th>

            <th>Ruta</th>

            <th>Estado</th>

            <th>Precio por pasaje</th>

            <th>Pasajes pagados</th>

            <th>Pasajes pendientes de pago</th>

            <th>Venta de pasajes</th>

            @if ($viewTasaServicio)
                <th>Tasas cobradas</th>
            @endif

        </tr>

    </thead>

    <tbody>

        @forelse($programaciones as $salida)
            <tr>
                <td>

                    @if ($canViewPassengers)
                        <a href="{{ route($routeProgramacion, $salida->id) }}"
                            wire:navigate>#{{ $salida->id }}</a>
                    @else
                        #{{ $salida->id }}
                    @endif

                </td>

                <td>
                    {{ $salida->getSalida()?->format('d/m/Y') }} {{ $salida->getSalida()?->format('H:i') }}
                </td>

                <td>
                    {{ $salida->viaje?->origenTerminal?->nombre }} →
                    {{ $salida->viaje?->destinoTerminal?->nombre }}
                </td>

                <td>
                    {{ match ($salida->estatus) { 1 => 'Activa', 2 => 'Inactiva', 3 => 'Finalizada', default => 'Eliminada' } }}
                </td>

                <td>
                    @php($precio = $salida->tramoPrecios->first()?->precio ?? 0)
                    <x-money.dual :usd="$precio" :bs="\App\Support\ConversorMoneda::aBolivares($precio, $tipoCambio)" />
                </td>

                <td>
                    <span class="badge text-bg-success">{{ $salida->pasajes_vendidos }}</span>
                </td>

                <td>
                    <span class="badge text-bg-warning">{{ $salida->pasajes_pendientes }}</span>
                </td>

                <td>
                    <x-money.dual :usd="$salida->ventas_total" :bs="$salida->ventas_total_bs" />
                </td>

                @if ($viewTasaServicio)
                    <td>
                        <x-money.dual :usd="$salida->tasas_servicio_total" :bs="$salida->tasas_servicio_total_bs" />
                    </td>
                @endif

            </tr>

        @empty

            <tr>
                <td colspan="{{ $viewTasaServicio ? 9 : 8 }}" class="text-center py-4">
                    No hay programaciones registradas.
                </td>

            </tr>
        @endforelse

    </tbody>
</table>


