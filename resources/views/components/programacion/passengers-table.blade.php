@props(['tickets', 'canReservasDetail'])

<div class="card">
    <div class="card-header">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
            <span>Pasajeros & Tramos Comercializados</span>
            <div style="width: 320px; max-width: 100%;">
                <label class="visually-hidden" for="buscar-pasajeros">Buscar pasajero</label>
                <input id="buscar-pasajeros" type="search" class="form-control" x-model.debounce.200ms="search"
                    placeholder="Buscar pasajero u origen/destino">
            </div>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr>
                    <th>Reserva</th>
                    <th>Pasajero</th>
                    <th>Documento</th>
                    <th>Fecha de nacimiento</th>
                    <th>Asiento</th>
                    <th>Tramo Comprado (Origen ➔ Destino)</th>
                    <th>Subtotal</th>
                    <th>Tasa Servicio</th>
                    <th>Estatus Reserva</th>
                    <th>Abordaje</th>
                    <th class="text-center">QR</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($tickets as $ticket)
                    <tr data-search="{{ $ticket->viajero?->nombre }} {{ $ticket->viajero?->apellido }} {{ $ticket->viajero?->documento_identidad }} {{ $ticket->numero_asiento }} {{ $ticket->reserva?->origenTerminal?->nombre }} {{ $ticket->reserva?->destinoTerminal?->nombre }}"
                        x-show="matches($el.dataset.search)">
                        <td>
                            @if ($canReservasDetail)
                                <a href="{{ route('admin.reservas.detail', $ticket->reserva_id) }}" wire:navigate>
                                    #{{ $ticket->reserva_id }}
                                </a>
                            @else
                                #{{ $ticket->reserva_id }}
                            @endif
                        </td>
                        <td>{{ $ticket->viajero?->nombre }} {{ $ticket->viajero?->apellido }}</td>
                        <td>{{ $ticket->viajero?->documento_identidad }}</td>
                        <td>{{ $ticket->viajero?->fecha_nacimiento?->format('d/m/Y') ?? '—' }}</td>
                        <td><span class="badge text-bg-info">Asiento {{ $ticket->numero_asiento ?? 'S/A' }}</span></td>
                        <td>
                            <span class="fw-semibold">{{ $ticket->reserva?->origenTerminal?->nombre ?? 'No registrado' }}</span>
                            <i class="bi bi-arrow-right text-muted mx-1"></i>
                            <span class="fw-semibold">{{ $ticket->reserva?->destinoTerminal?->nombre ?? 'No registrado' }}</span>
                        </td>
                        <td class="fw-bold text-success">
                            <x-money.dual :usd="$ticket->subtotal" :bs="$ticket->calcularMontoBs($ticket->subtotal)" />
                        </td>
                        <td>
                            <x-money.dual :usd="$ticket->tasa_servicio" :bs="$ticket->calcularMontoBs($ticket->tasa_servicio)" />
                        </td>
                        <td>{{ $ticket->reserva?->getStatusPago() }}</td>
                        <td>{{ $ticket->abordado ? 'Abordado' : 'Pendiente' }}</td>
                        <td class="text-center"><x-list.pasaje-qr :pasaje="$ticket" /></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="11" class="text-center py-4">No hay pasajeros registrados en esta salida.</td>
                    </tr>
                @endforelse

                @if ($tickets->isNotEmpty())
                    <tr x-cloak x-show="search && !hasMatches()">
                        <td colspan="11" class="text-center py-4">No hay coincidencias.</td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>
</div>
