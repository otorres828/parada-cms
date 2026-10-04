{{--
    TABLA DE PROGRAMACIONES DE RUTA | Presenta el historial de salidas y sus ventas.
--}}

@props(['programaciones', 'canViewPassengers','viewTasaServicio'=>true])

<table class="table align-middle mb-0">

    <thead>

        <tr>
            <th>Programación</th>

            <th>Salida</th>

            <th>Estado</th>

            <th>Pasajes vendidos</th>

            @if ($viewTasaServicio)

                <th>Tasas de servicio</th>
                
            @endif

        </tr>
    </thead>

    <tbody>

        @forelse($programaciones as $salida)
            <tr>
                <td>

                    @if ($canViewPassengers)
                        <a href="{{ route('admin.programaciones.detail', $salida->id) }}"
                            wire:navigate>#{{ $salida->id }}</a>
                    @else
                        #{{ $salida->id }}
                    @endif

                </td>

                <td>
                    {{ $salida->getSalida()?->format('d/m/Y') }} {{ $salida->getSalida()?->format('H:i') }}
                </td>

                <td>
                    {{ $salida->estatus ? 'Activa' : 'Inactiva' }}
                </td>

                <td>
                    {{ $salida->pasajes_vendidos }}
                </td>

                @if ($viewTasaServicio)
                    
                    <td>
                        <x-money.dual :usd="$salida->tasas_servicio_total" :bs="$salida->tasas_servicio_total_bs" />
                    </td>
                    
                @endif

            </tr>

        @empty

            <tr>
                <td colspan="6" class="text-center py-4">
                    No hay programaciones registradas.
                </td>

            </tr>
        @endforelse

    </tbody>
</table>


