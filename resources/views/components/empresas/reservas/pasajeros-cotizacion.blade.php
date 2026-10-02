{{-- Pasajeros temporales: retirar no modifica la base de datos. --}}
@props(['pasajeros', 'precio'])
<div class="card mb-3">
    <div class="card-header">Pasajeros de la cotización</div>
    <div class="table-responsive">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th>Pasajero</th>
                    <th>Tipo</th>
                    <th>Asiento</th>
                    <th>Precio</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($pasajeros as $indice => $persona)
                    @php($ocupa = $persona['tipo_pasajero'] !== 'infante' || !empty($persona['con_asiento']))
                    <tr wire:key="pasajero-cotizado-{{ $indice }}">
                        <td>{{ $persona['nombre'] }} {{ $persona['apellido'] }}</td>
                        <td>{{ ucfirst($persona['tipo_pasajero']) }}</td>
                        <td>{{ $ocupa ? 'Se asigna al registrar' : 'Sin asiento' }}</td>
                        <td>${{ number_format($ocupa ? $precio : 0, 2) }}</td>
                        <td class="text-end"><button type="button" class="btn btn-outline-danger btn-sm"
                                wire:click="removerPasajero({{ $indice }})">Retirar</button></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5">Agrega los pasajeros para calcular el total.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
