{{-- Paradas ordenadas. El selector para añadir paradas solo aparece durante el alta. --}}
@props(['terminales', 'paradas', 'viajeId', 'estados', 'terminalesOrigen', 'terminalesParada'])

<div class="card">

    <div class="card-header fw-semibold">
        1. Recorrido de la ruta
    </div>

    <div class="card-body">

        @if ($viajeId === null)
            <div class="row g-2 mb-3">
                <div class="col-sm-4">
                    <label class="form-label" for="viaje-estado-origen">Estado</label>
                    <select id="viaje-estado-origen" class="form-select" wire:model.live="estadoOrigenId">
                        <option value="">Todos los estados</option>
                        @foreach ($estados as $estado)
                            <option value="{{ $estado->id }}">{{ $estado->nombre }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-sm-8">
                    <label class="form-label" for="viaje-origen">Terminal de origen</label>
                    <select id="viaje-origen" class="form-select" wire:model.live="origenId" required>
                        <option value="">Seleccionar origen</option>
                        @foreach ($terminalesOrigen as $terminal)
                            <option value="{{ $terminal->id }}">{{ $terminal->nombre }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        @endif

        @if ($viajeId !== null)
            <p class="small text-muted">
                El origen y el destino son fijos. No se pueden eliminar paradas ni tramos existentes.
            </p>
        @else
            <div class="row g-2 mb-4 align-items-end">
                <div class="col-sm-4">
                    <label class="form-label" for="viaje-estado-parada">Estado</label>
                    <select id="viaje-estado-parada" class="form-select" wire:model.live="estadoParadaId">
                        <option value="">Todos los estados</option>
                        @foreach ($estados as $estado)
                            <option value="{{ $estado->id }}">{{ $estado->nombre }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-sm-8">
                    <label class="form-label" for="viaje-parada">Destino o parada</label>
                    <div class="d-flex align-items-center gap-2">
                        <select id="viaje-parada" class="form-select flex-grow-1" wire:model="terminalId"
                            style="width: 0; min-width: 0;"
                            @disabled(count($paradas) === 0)>
                            <option value="">Seleccionar terminal</option>
                            @foreach ($terminalesParada as $terminal)
                                @if (! in_array($terminal->id, $paradas))
                                    <option value="{{ $terminal->id }}">{{ $terminal->nombre }}</option>
                                @endif
                            @endforeach
                        </select>

                        <button type="button" class="btn btn-outline-primary d-inline-flex align-items-center gap-1 text-nowrap flex-shrink-0"
                            wire:click="agregarParada"
                            wire:loading.attr="disabled" @disabled(count($paradas) === 0)>
                            <i class="bi bi-plus-lg" aria-hidden="true"></i> Añadir
                        </button>
                    </div>
                </div>
            </div>

            @error('terminalId')
                <div class="text-danger small mb-3">{{ $message }}</div>
            @enderror
        @endif

        <p class="small text-muted mb-3">
            La duración en minutos corresponde al recorrido desde la parada anterior.
        </p>

        <ol class="list-unstyled mb-0">
            @foreach ($paradas as $indice => $terminalId)
                <li class="border-start border-primary border-3 ps-3 pb-3"
                    wire:key="viaje-parada-{{ $terminalId }}">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                        <div class="flex-grow-1" style="min-width: 0;">
                            <span class="badge bg-primary rounded-pill me-1">{{ chr(65 + $indice) }}</span>
                            <strong>{{ $terminales->get($terminalId)?->nombre ?? 'Terminal no disponible' }}</strong>
                            <div class="small text-muted mt-1">
                                {{ $indice === 0 ? 'Origen' : ($loop->last ? 'Destino final' : 'Parada intermedia') }}
                            </div>
                        </div>

                        @if ($indice > 0)
                            @php($clave = $paradas[$indice - 1].'-'.$terminalId)

                            <div class="d-flex align-items-center gap-2 ms-auto">
                                <div style="width: 120px;">
                                    <label class="visually-hidden" for="duracion-{{ $clave }}">
                                        Minutos desde la parada anterior
                                    </label>
                                    <div class="input-group input-group-sm">
                                        <input id="duracion-{{ $clave }}" class="form-control text-center"
                                            type="number" min="1" max="1440" step="1"
                                            wire:model="minutos.{{ $clave }}"
                                            title="Duración desde la parada anterior" required>
                                        <span class="input-group-text">min</span>
                                    </div>
                                </div>

                                @if ($viajeId === null)
                                    <button type="button" class="btn btn-sm btn-outline-danger"
                                        wire:click="removerParada({{ $indice }})" wire:loading.attr="disabled"
                                        aria-label="Retirar parada">
                                        <i class="bi bi-x-lg" aria-hidden="true"></i>
                                    </button>
                                @endif
                            </div>
                        @endif
                    </div>

                    @if ($indice > 0)
                        @error('minutos.'.$clave)
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    @endif
                </li>
            @endforeach
        </ol>

        @if (count($paradas) < 2)
            <p class="text-muted small mb-0">Selecciona un origen y añade al menos un destino.</p>
        @endif

    </div>

</div>
