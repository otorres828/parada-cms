{{--
    RUTAS DE VIAJES — DETALLE
    --------------------------------------------------------------------------
    Muestra el origen, destino, empresa, itinerario físico y la Matriz Comercial de Precios O&D.
    Incluye su historial de salidas programadas.
--}}

@section('title', 'Detalle de Ruta')

<div x-data="detailViaje" class="py-3">

    <x-list.heading>

        <x-slot:title>
            Detalle de Ruta @if ($viaje_id)
                <small class="text-body-secondary">#{{ $viaje_id }}</small>

            @endif

        </x-slot:title>

        <x-slot:button>

            <x-form.cancel-button :link="route('admin.viajes.list')">
                Volver al listado
            </x-form.cancel-button>

        </x-slot:button>

    </x-list.heading>

    <div class="container-fluid px-0 mb-4">

        <div class="row g-3">

            {{-- Columna Izquierda: Información de la Ruta e Itinerario de Paradas --}}
            <div class="col-md-6">

                <div class="card h-100">

                    <div class="card-header fw-semibold">
                        <i class="bi bi-info-circle me-1" aria-hidden="true"></i> Información & Secuencia de Paradas
                    </div>

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

                        <div x-data="{ modoDetallado: false }" class="bg-body-tertiary rounded border-start border-primary border-3 p-3 mt-3">

                            <div class="d-flex align-items-center justify-content-between mb-2 flex-wrap gap-2">
                                <div class="fw-semibold">
                                    <i class="bi bi-signpost-split me-1" aria-hidden="true"></i>
                                    Secuencia de paradas e itinerario
                                </div>
                                @if ($viaje->tramos->isNotEmpty())
                                    <div class="form-check form-switch mb-0 small">
                                        <input class="form-check-input cursor-pointer" type="checkbox" role="switch" id="toggleModoDetallado" x-model="modoDetallado">
                                        <label class="form-check-label text-body-secondary cursor-pointer" for="toggleModoDetallado" x-text="modoDetallado ? 'Desglose por tramos' : 'Vista simple'"></label>
                                    </div>
                                @endif
                            </div>

                            @if ($viaje->tramos->isNotEmpty())
                                {{-- Vista Simple: Secuencia de Terminales --}}
                                <div x-show="!modoDetallado">
                                    <ol class="list-group list-group-numbered list-group-flush mb-0">
                                        @foreach ($viaje->tramos as $index => $tramo)
                                            <li class="list-group-item bg-transparent d-flex justify-content-between align-items-center px-0 py-1">
                                                <div class="ms-2 me-auto fw-bold text-start">
                                                    {{ $tramo->origenTerminal?->nombre }}
                                                </div>
                                                @if ($loop->first)
                                                    <span class="badge text-bg-success rounded-pill small">Origen</span>
                                                @else
                                                    <span class="badge text-bg-secondary rounded-pill small">Parada Intermedia</span>
                                                @endif
                                            </li>
                                            @if ($loop->last)
                                                <li class="list-group-item bg-transparent d-flex justify-content-between align-items-center px-0 py-1">
                                                    <div class="ms-2 me-auto fw-bold text-start">
                                                        {{ $tramo->destinoTerminal?->nombre }}
                                                    </div>
                                                    <span class="badge text-bg-dark rounded-pill small">Destino Final</span>
                                                </li>
                                            @endif
                                        @endforeach
                                    </ol>
                                </div>

                                {{-- Vista Detallada: Tramo por Tramo con duraciones --}}
                                <div x-show="modoDetallado" x-cloak>
                                    <ol class="list-group list-group-numbered list-group-flush mb-0">
                                        @foreach ($viaje->tramos as $tramo)
                                            <li class="list-group-item bg-transparent d-flex justify-content-between align-items-start px-0 py-2">
                                                <div class="ms-2 me-auto">
                                                    <div class="fw-bold">
                                                        {{ $tramo->origenTerminal?->nombre }} → {{ $tramo->destinoTerminal?->nombre }}
                                                    </div>
                                                    @if ($tramo->duracion_estimada)
                                                        <small class="text-body-secondary">Duración tramo: {{ $tramo->duracion_estimada }}</small>
                                                    @endif
                                                </div>
                                                <span class="badge text-bg-primary rounded-pill">Tramo #{{ $tramo->orden }}</span>
                                            </li>
                                        @endforeach
                                    </ol>
                                </div>
                            @else
                                <div class="text-body-secondary">Sin paradas intermedias registradas.</div>
                            @endif

                        </div>

                    </div>

                </div>

            </div>

            {{-- Columna Derecha: Matriz Comercial de Precios por Tramo (O&D) --}}
            <div class="col-md-6">

                <div class="card h-100">

                    <div class="card-header fw-semibold">
                        <i class="bi bi-tags me-1" aria-hidden="true"></i> Matriz Comercial de Precios por Tramo (O&D)
                    </div>

                    <div class="card-body">

                        @php
                            $tramoPreciosRecientes = $viaje->programaciones->first()?->tramoPrecios ?? collect();
                        @endphp

                        @if ($tramoPreciosRecientes->isNotEmpty())
                            <div class="table-responsive">
                                <table class="table table-sm align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th>Tramo Comercial</th>
                                            <th class="text-end">Precio Configurado</th>
                                            <th class="text-center">Tope Asientos</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($tramoPreciosRecientes as $tp)
                                            <tr>
                                                <td>
                                                    <span class="fw-semibold">{{ $tp->origenTerminal?->nombre }}</span>
                                                    <i class="bi bi-arrow-right text-muted mx-1"></i>
                                                    <span class="fw-semibold">{{ $tp->destinoTerminal?->nombre }}</span>
                                                </td>
                                                <td class="text-end text-success fw-bold">
                                                    USD {{ number_format($tp->precio, 2) }}
                                                </td>
                                                <td class="text-center">
                                                    @if ($tp->asientos_maximos_permitidos)
                                                        <span class="badge text-bg-warning">{{ $tp->asientos_maximos_permitidos }} asientos</span>
                                                    @else
                                                        <span class="badge text-bg-secondary">Sin tope (Libre)</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-body-secondary py-3 text-center">
                                No se han configurado tarifas O&D en las salidas programadas de esta ruta.
                            </div>
                        @endif

                    </div>

                </div>

            </div>

        </div>

    </div>

    <div class="card mt-4">

        <div class="card-header fw-semibold">
            <i class="bi bi-calendar-event me-1" aria-hidden="true"></i> Historial de salidas programadas
        </div>

        <div class="table-responsive">

            <table class="table align-middle mb-0">

                <thead>

                    <tr>
                        <th>Programación</th>

                        <th>Salida</th>

                        <th>Ruta</th>

                        <th>Estado</th>

                        <th>Pasajes vendidos</th>

                        <th>Tasas de servicio USD</th>

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
                                {{ $salida->viaje?->origenTerminal?->nombre }} → {{ $salida->viaje?->destinoTerminal?->nombre }}
                            </td>

                            <td>
                                {{ $salida->estatus ? 'Activa' : 'Inactiva' }}
                            </td>

                            <td>
                                {{ $salida->pasajes_vendidos }}
                            </td>

                            <td>
                                {{ number_format($salida->tasas_servicio_total ?? 0, 2) }}
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

        </div>

    </div>

    <x-layout.loader.fullpage wire:loading.delay.short />

</div>

@script
    <script>
        Alpine.data('detailViaje', () => ({
            destroy() {
                this.toastCleanup?.forEach(cleanup => cleanup());
            },
            init() {
                this.toastCleanup = [
                    Livewire.on('successEventList', data => this.$store.toast.success(data.message)),
                    Livewire.on('errorEventList', data => this.$store.toast.info(data.message)),
                ];
                const savedMessage = @js(session()->pull('admin_success'));
                if (savedMessage) this.$nextTick(() => Livewire.dispatch('successEventList', {
                    message: savedMessage
                }));
            },

        }));
    </script>
@endscript
