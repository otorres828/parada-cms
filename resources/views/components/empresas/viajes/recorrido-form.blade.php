{{-- Paradas ordenadas. En edición se conserva el origen y el destino final. --}}
@props(['terminales', 'paradas', 'viajeId'])

<div class="card">
    <div class="card-header fw-semibold">1. Recorrido de la ruta</div>
    <div class="card-body">
        <label class="form-label" for="viaje-origen">Terminal de origen</label>
        <select id="viaje-origen" class="form-select mb-3" wire:model.live="origenId" required @disabled($viajeId !== null)>
            <option value="">Seleccionar origen</option>
            @foreach ($terminales as $terminal)
                <option value="{{ $terminal->id }}">{{ $terminal->nombre }}</option>
            @endforeach
        </select>
        @if ($viajeId)
            <p class="small text-muted">No se pueden eliminar paradas ni tramos existentes. Origen y destino fijos. Las nuevas paradas se insertan antes del destino final. Si la ruta tiene programaciones, conserva el recorrido.</p>
        @endif
        <label class="form-label" for="viaje-parada">{{ $viajeId ? 'Parada intermedia' : 'Destino o parada' }}</label>
        <div class="d-flex gap-2 mb-4">
            <select id="viaje-parada" class="form-select" wire:model="terminalId" @disabled(count($paradas) === 0)>
                <option value="">Seleccionar terminal</option>
                @foreach ($terminales as $terminal)
                    @if (!in_array($terminal->id, $paradas))
                        <option value="{{ $terminal->id }}">{{ $terminal->nombre }}</option>
                    @endif
                @endforeach
            </select>
            <button type="button" class="btn btn-outline-primary" wire:click="agregarParada" wire:loading.attr="disabled" @disabled(count($paradas) === 0)>
                <i class="bi bi-plus-lg" aria-hidden="true"></i> Añadir
            </button>
        </div>
        @error('terminalId') <div class="text-danger small mb-3">{{ $message }}</div> @enderror
        <p class="small text-muted mb-3">La duración en minutos corresponde al recorrido desde la parada anterior.</p>
        <ol class="list-unstyled mb-0">
            @foreach ($paradas as $indice => $terminalId)
                <li class="border-start border-primary border-3 ps-3 pb-3" wire:key="viaje-parada-{{ $terminalId }}">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                        <div class="flex-grow-1" style="min-width: 0;">
                            <span class="badge bg-primary rounded-pill me-1">{{ chr(65 + $indice) }}</span>
                            <strong>{{ $terminales->get($terminalId)?->nombre ?? 'Terminal no disponible' }}</strong>
                            <div class="small text-muted mt-1">{{ $indice === 0 ? 'Origen' : ($loop->last ? 'Destino final' : 'Parada intermedia') }}</div>
                        </div>
                        @if ($indice > 0)
                            @php($clave = $paradas[$indice - 1].'-'.$terminalId)
                            <div class="d-flex align-items-center gap-2 ms-auto">
                                <div style="width: 120px;">
                                    <label class="visually-hidden" for="duracion-{{ $clave }}">Minutos desde la parada anterior</label>
                                    <div class="input-group input-group-sm">
                                        <input id="duracion-{{ $clave }}" class="form-control text-center" type="number" min="1" max="1440" step="1" wire:model="minutos.{{ $clave }}" title="Duración desde la parada anterior" required>
                                        <span class="input-group-text">min</span>
                                    </div>
                                </div>
                                @if ($viajeId === null)
                                    <button type="button" class="btn btn-sm btn-outline-danger" wire:click="removerParada({{ $indice }})" wire:loading.attr="disabled" aria-label="Retirar parada">
                                        <i class="bi bi-x-lg" aria-hidden="true"></i>
                                    </button>
                                @endif
                            </div>
                        @endif
                    </div>
                    @if ($indice > 0)
                        @error('minutos.'.$clave) <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    @endif
                </li>
            @endforeach
        </ol>
        @if (count($paradas) < 2)
            <p class="text-muted small mb-0">Selecciona un origen y añade al menos un destino.</p>
        @endif
    </div>
</div>
