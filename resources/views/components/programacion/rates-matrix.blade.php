@props(['programacion', 'disponibilidadTramos', 'tipoCambio'])

<div class="card h-100">
    <div class="card-header fw-semibold">
        <i class="bi bi-tags me-1" aria-hidden="true"></i>
        Matriz de Precios por Trayecto (Salida #{{ $programacion->id }})
    </div>

    <div class="card-body">
        @if ($programacion->tramoPrecios->isNotEmpty())
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Tramo Comercial</th>
                            <th>Salida del tramo</th>
                            <th>Llegada al destino</th>
                            <th class="text-end">Precio</th>
                            <th class="text-center">Tope Asientos</th>
                            <th class="text-center">Ocupados</th>
                            <th class="text-center">Disponibles</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($programacion->tramoPrecios as $tramoPrecio)
                            <tr>
                                <td>
                                    <span class="fw-semibold">{{ $tramoPrecio->origenTerminal?->nombre }}</span>
                                    <i class="bi bi-arrow-right text-muted mx-1"></i>
                                    <span class="fw-semibold">{{ $tramoPrecio->destinoTerminal?->nombre }}</span>
                                </td>
                                <td>{{ $tramoPrecio->getSalida()?->format('d/m/Y H:i') ?? 'Sin horario' }}</td>
                                <td>{{ $tramoPrecio->getLlegada()?->format('d/m/Y H:i') ?? 'Sin horario' }}</td>
                                <td class="text-end text-success fw-bold">
                                    <x-money.dual :usd="$tramoPrecio->precio" :bs="$tramoPrecio->calcularMontoBs($tipoCambio)" />
                                </td>
                                <td class="text-center">
                                    @if ($tramoPrecio->asientos_maximos_permitidos !== null)
                                        <span class="badge text-bg-warning">
                                            {{ $tramoPrecio->asientos_maximos_permitidos }} asientos
                                        </span>
                                    @else
                                        <span class="badge text-bg-secondary">Sin tope (Libre)</span>
                                    @endif
                                </td>
                                <td class="text-center">{{ $disponibilidadTramos[$tramoPrecio->id]['ocupados'] }}</td>
                                <td class="text-center">{{ $disponibilidadTramos[$tramoPrecio->id]['disponibles'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <p class="small text-body-secondary mt-3 mb-0">
                Ocupados: asientos de reservas pagadas, pendientes y nuevas sin vencer que coinciden con este tramo.
                Disponibles = tope de asientos menos ocupados; sin tope se usa la capacidad del transporte.
            </p>
        @else
            <div class="text-body-secondary py-3 text-center">
                No hay matriz de tarifas O&D configurada para esta salida.
            </div>
        @endif
    </div>
</div>
