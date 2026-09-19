{{--
    RUTAS DE VIAJES — DETALLE
    --------------------------------------------------------------------------
    Muestra el origen, destino, empresa y duración de la ruta. Incluye su historial de
    programaciones, pasajes vendidos y tasas de servicio.

    Componentes reutilizables utilizados:
    - <x-list.heading />: Cabecera del módulo con título y acciones.
    - <x-form.cancel-button />: Enlace para regresar o cancelar la edición.
    - <x-list.status-badge />: Etiqueta visual del estado del registro.
    - <x-layout.loader.fullpage />: Indicador de carga durante las operaciones de Livewire.
    --------------------------------------------------------------------------
--}}

@section('title', 'Rutas de viajes')

<div x-data="detailViaje" class="py-3">

    <x-list.heading>

        <x-slot:title>
            Rutas de viajes @if ($viaje_id)
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

        <div class="row">

            <div class="col-md-6">

                <div class="card">

                    <div class="card-body">

                        <dl class="row mb-0">
                            <dt class="col-sm-4">Empresa</dt>
                            <dd class="col-sm-8">
                                {{ $viaje->empresa?->nombre ?? '—' }}
                            </dd>
                            <dt class="col-sm-4">Origen</dt>
                            <dd class="col-sm-8">
                                {{ $viaje->origenTerminal?->nombre ?? '—' }}
                            </dd>
                            <dt class="col-sm-4">Destino</dt>
                            <dd class="col-sm-8">
                                {{ $viaje->destinoTerminal?->nombre ?? '—' }}
                            </dd>
                            <dt class="col-sm-4">Duración</dt>
                            <dd class="col-sm-8">
                                {{ $viaje->duracion_estimada ?? '—' }}
                            </dd>
                            <dt class="col-sm-4">Estado</dt>
                            <dd class="col-sm-8">
                                <x-list.status-badge :status="$viaje->estatus" />
                            </dd>
                        </dl>

                    </div>

                </div>

            </div>

        </div>

    </div>

    <div class="card mt-4">

        <div class="card-header">
            Historial de programaciones
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
