{{-- Pasajeros temporales: retirar no modifica la base de datos. --}}
@props(['pasajeros', 'precio', 'cambio'])

<div class="card mb-3"
    :class="{ 'border-primary': resaltados.pasajeros }"
    style="transition: border-color 250ms ease;">

    <div class="card-header">Pasajeros de la cotización</div>

    <x-list.table>

        <thead>

            <tr>
                <th>Pasajero</th>

                <th>Tipo</th>

                <th>Asiento</th>

                <th class="text-end">Precio</th>

                <th></th>

            </tr>
        </thead>

        <tbody>

            @forelse ($pasajeros as $indice => $persona)

                @php($ocupa = $persona['tipo_pasajero'] !== 'infante' || !empty($persona['con_asiento']))

                <tr wire:key="pasajero-cotizado-{{ $indice }}">

                    <td>
                        {{ $persona['nombre'] }} {{ $persona['apellido'] }}
                    </td>

                    <td>
                        {{ ucfirst($persona['tipo_pasajero']) }}
                    </td>

                    <td>
                        {{ $ocupa ? 'Se asigna al registrar' : 'Sin asiento' }}
                    </td>

                    <td class="text-end">
                        <span class="d-block text-nowrap">USD {{ number_format($ocupa ? $precio : 0, 2) }}</span>
                        <small class="d-block text-muted text-nowrap">BS {{ number_format(($ocupa ? $precio : 0) * (float) $cambio?->valor_usd, 2, ',', '.') }}</small>
                    </td>

                    <td class="text-end">
                        <button type="button" class="btn btn-outline-danger btn-sm" wire:click="removerPasajero({{ $indice }})">Retirar</button>
                    </td>

                </tr>

            @empty

                <tr>
                    <td colspan="5">
                        Agrega los pasajeros para calcular el total.
                    </td>

                </tr>

            @endforelse

        </tbody>

    </x-list.table>

</div>
