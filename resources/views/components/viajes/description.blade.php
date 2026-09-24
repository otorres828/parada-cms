{{--
    DESCRIPCIÓN DE RUTA | Presenta datos generales, estado y secuencia de paradas.
--}}

@props(['viaje'])

<div class="card-body">

    <dl class="row mb-0">
        <dt class="col-sm-4">Empresa</dt>
        <dd class="col-sm-8">
            {{ $viaje->empresa?->nombre ?? '—' }}
        </dd>
        <dt class="col-sm-4">Origen Principal</dt>
        <dd class="col-sm-8">
            {{ $viaje->origenTerminal?->nombre ?? '—' }}
        </dd>
        <dt class="col-sm-4">Destino Principal</dt>
        <dd class="col-sm-8">
            {{ $viaje->destinoTerminal?->nombre ?? '—' }}
        </dd>
        <dt class="col-sm-4">Duración Total</dt>
        <dd class="col-sm-8">
            {{ $viaje->duracion_estimada ?? '—' }}
        </dd>
        <dt class="col-sm-4">Estado Ruta</dt>
        <dd class="col-sm-8">
            <x-list.status-badge :status="$viaje->estatus" />
        </dd>
    </dl>

    <div x-data="{ modoDetallado: false }"
        class="bg-body-tertiary rounded border-start border-primary border-3 p-3 mt-3">

        <div class="d-flex align-items-center justify-content-between mb-2 flex-wrap gap-2">
            <div class="fw-semibold">
                <i class="bi bi-signpost-split me-1" aria-hidden="true"></i>
                Secuencia de paradas e itinerario
            </div>
            @if ($viaje->tramos->isNotEmpty())
                <div class="form-check form-switch mb-0 small">
                    <input class="form-check-input cursor-pointer" type="checkbox" role="switch"
                        id="toggleModoDetallado" x-model="modoDetallado">
                    <label class="form-check-label text-body-secondary cursor-pointer"
                        for="toggleModoDetallado"
                        x-text="modoDetallado ? 'Desglose por tramos' : 'Vista simple'"></label>
                </div>
            @endif
        </div>

        @if ($viaje->tramos->isNotEmpty())
            {{-- Vista Simple: Secuencia de Terminales --}}
            <div x-show="!modoDetallado">
                <ol class="list-group list-group-numbered list-group-flush mb-0">
                    @foreach ($viaje->tramos as $index => $tramo)
                        <li
                            class="list-group-item bg-transparent d-flex justify-content-between align-items-center px-0 py-1">
                            <div class="ms-2 me-auto fw-bold text-start">
                                {{ $tramo->origenTerminal?->nombre }}
                            </div>
                            @if ($loop->first)
                                <span class="badge text-bg-success rounded-pill small">Origen</span>
                            @else
                                <span class="badge text-bg-secondary rounded-pill small">Parada
                                    Intermedia</span>
                            @endif
                        </li>
                        @if ($loop->last)
                            <li
                                class="list-group-item bg-transparent d-flex justify-content-between align-items-center px-0 py-1">
                                <div class="ms-2 me-auto fw-bold text-start">
                                    {{ $tramo->destinoTerminal?->nombre }}
                                </div>
                                <span class="badge text-bg-dark rounded-pill small">Destino
                                    Final</span>
                            </li>
                        @endif
                    @endforeach
                </ol>
            </div>

            {{-- Vista Detallada: Tramo por Tramo con duraciones --}}
            <div x-show="modoDetallado" x-cloak>
                <ol class="list-group list-group-numbered list-group-flush mb-0">
                    @foreach ($viaje->tramos as $tramo)
                        <li
                            class="list-group-item bg-transparent d-flex justify-content-between align-items-start px-0 py-2">
                            <div class="ms-2 me-auto">
                                <div class="fw-bold">
                                    {{ $tramo->origenTerminal?->nombre }} →
                                    {{ $tramo->destinoTerminal?->nombre }}
                                </div>
                                @if ($tramo->duracion_estimada)
                                    <small class="text-body-secondary">Duración tramo:
                                        {{ $tramo->duracion_estimada }}</small>
                                @endif
                            </div>
                            <span class="badge text-bg-primary rounded-pill">Tramo
                                #{{ $tramo->orden }}</span>
                        </li>
                    @endforeach
                </ol>
            </div>
        @else
            <div class="text-body-secondary">Sin paradas intermedias registradas.</div>
        @endif

    </div>

</div>

