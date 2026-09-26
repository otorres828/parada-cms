{{--
    TABLA DE PROGRAMACIONES DE RUTA | Presenta el historial de salidas y sus ventas.
--}}

@props(['programaciones', 'canViewPassengers'])

<table class="table align-middle mb-0">

    <thead>

        <tr>
            <th>Programación</th>

            <th>Salida</th>

            <th>Estado</th>

            <th>Pasajes vendidos</th>

            <th>Tasas de servicio</th>

        </tr>
    </thead>

    <tbody>

        @forelse($programaciones as $salida)
            <tr>
                <td>

                    @if ($canViewPassengers)
                        <a href="{{ route('admin.programaciones.passengers', $salida->id) }}"
                            wire:navigate>#{{ $salida->id }}</a>
                    @else
                        #{{ $salida->id }}
                    @endif

                </td>

                <td>
                    {{ $salida->fecha_salida->format('d/m/Y') }} {{ substr($salida->hora_salida, 0, 5) }}
                </td>

                <td>
                    {{ $salida->estatus ? 'Activa' : 'Inactiva' }}
                </td>

                <td>
                    {{ $salida->pasajes_vendidos }}
                </td>

                <td>
                    <x-money.dual :usd="$salida->tasas_servicio_total" :bs="$salida->tasas_servicio_total_bs" />
                </td>

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


