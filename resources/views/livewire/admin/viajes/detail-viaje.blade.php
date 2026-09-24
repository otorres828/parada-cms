{{--
    RUTAS DE VIAJES — DETALLE
    --------------------------------------------------------------------------
    Presenta la empresa, los terminales principales y la secuencia ordenada de tramos físicos.
    Permite alternar entre el itinerario simple y su desglose con duraciones. Muestra las tarifas
    de una programación asociada y el historial de salidas con pasajes vendidos y tasas de
    servicio.

    Componentes reutilizables utilizados:
    - <x-list.heading />: Cabecera del módulo con título y acciones.
    - <x-form.cancel-button />: Enlace para regresar o cancelar la edición.
    - <x-list.status-badge />: Etiqueta visual del estado del registro.
    - <x-layout.loader.fullpage />: Indicador de carga durante las operaciones de Livewire.
    --------------------------------------------------------------------------
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

                    <x-viajes.description :viaje="$viaje" />

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
                                <x-viajes.tramo-precios-table :tramo-precios="$tramoPreciosRecientes" />
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

            <x-viajes.programaciones-table :programaciones="$programaciones" :can-view-passengers="$canViewPassengers" />

        </div>

    </div>

    {{ $programaciones->links() }}

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



