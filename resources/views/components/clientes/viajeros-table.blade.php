{{--
    TABLA DE VIAJEROS DEL CLIENTE | Muestra las personas guardadas por el cliente para sus viajes.
--}}

@props(['viajeros'])

<div class="card h-100">

    <div class="card-header">
        Viajeros asociados
    </div>

    <div class="table-responsive">

        <table class="table align-middle mb-0">

            <thead>

                <tr>
                    <th>Viajero</th>

                    <th>Nacimiento</th>

                    <th>Documento</th>

                    <th>Tipo</th>

                    <th>Estatus</th>

                </tr>

            </thead>

            <tbody>

                @forelse ($viajeros as $viajero)

                    <tr>
                        <td>{{ $viajero->nombre }} {{ $viajero->apellido }}</td>

                        <td>{{ $viajero->fecha_nacimiento?->format('d/m/Y') ?? '—' }}</td>

                        <td>
                            {{ $viajero->getTipoDocumento() }}
                            <div class="text-body-secondary small">
                                {{ $viajero->documento_identidad ?? 'Sin documento' }}
                            </div>
                        </td>

                        <td>{{ ucfirst($viajero->tipo_pasajero) }}</td>

                        <td>
                            <x-list.status-badge :status="$viajero->estatus" />
                        </td>

                    </tr>

                @empty

                    <tr>
                        <td colspan="5" class="text-center py-4">
                            El cliente no tiene viajeros asociados.
                        </td>

                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>

</div>
