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
        <ol class="list-unstyled mb-0">
            @foreach ($paradas as $indice => $terminalId)
                <li class="border-start border-primary border-3 ps-3 pb-3" wire:key="viaje-parada-{{ $terminalId }}">
                    <div class="d-flex justify-content-between align-items-center gap-2">
                        <div>
                            <span class="badge bg-primary rounded-pill me-1">{{ chr(65 + $indice) }}</span>
                            <strong>{{ $terminales->get($terminalId)?->nombre ?? 'Terminal no disponible' }}</strong>
                            <div class="small text-muted mt-1">{{ $indice === 0 ? 'Origen' : ($loop->last ? 'Destino final' : 'Parada intermedia') }}</div>
                        </div>
                        @if ($indice > 0 && $viajeId === null)
                            <button type="button" class="btn btn-sm btn-outline-danger" wire:click="removerParada({{ $indice }})" wire:loading.attr="disabled" aria-label="Retirar parada">
                                <i class="bi bi-x-lg" aria-hidden="true"></i>
                            </button>
                        @endif
                    </div>
                    @if ($indice > 0)
                        @php($clave = $paradas[$indice - 1].'-'.$terminalId)
                        <label class="form-label small mt-2" for="duracion-{{ $clave }}">Minutos desde la parada anterior</label>
                        <input id="duracion-{{ $clave }}" class="form-control form-control-sm" type="number" min="1" max="1440" step="1" wire:model="minutos.{{ $clave }}" required>
                        @error('minutos.'.$clave) <div class="text-danger small">{{ $message }}</div> @enderror
                    @endif
                </li>
            @endforeach
        </ol>
        @if (count($paradas) < 2)
            <p class="text-muted small mb-0">Selecciona un origen y añade al menos un destino.</p>
        @endif
    </div>
</div>
