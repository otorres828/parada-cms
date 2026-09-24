{{--
    CAMPAÑAS — DETALLE
    --------------------------------------------------------------------------
    Muestra las condiciones de la campaña y sus cupones, con búsqueda, ordenación y paginación.
    Permite generar los códigos cuando la campaña y los permisos lo autorizan.

    Componentes reutilizables utilizados:
    - <x-list.heading />: Cabecera del módulo con título y acciones.
    - <x-form.cancel-button />: Enlace para regresar o cancelar la edición.
    - <x-layout.error />: Resumen de los errores de validación de Livewire.
    - <x-list.status-badge />: Etiqueta visual del estado del registro.
    - <x-list.actions />: Contenedor del buscador y las acciones del listado.
    - <x-list.search-input />: Buscador vinculado al estado del listado.
    - <x-list.table />: Contenedor reutilizable de la tabla del listado.
    - <x-list.sortable-button />: Control para ordenar por una columna.
    - <x-layout.loader.fullpage />: Indicador de carga durante las operaciones de Livewire.
    --------------------------------------------------------------------------
--}}

@section('title', 'Campañas')

<div x-data="detailCampana" class="py-3">

    <x-list.heading>

        <x-slot:title>
            Campañas @if ($configuracion_cupon_id)
                <small class="text-body-secondary">#{{ $configuracion_cupon_id }}</small>
            @endif

        </x-slot:title>

        <x-slot:button>

            <x-form.cancel-button :link="route('admin.cupones.list')">
                Volver al listado
            </x-form.cancel-button>

        </x-slot:button>

    </x-list.heading>

    <x-layout.error />

    <div class="container-fluid px-0 mb-4">

        <div class="row">

            <div class="col-md-6">

                <div class="card">

                    <x-cupones.description :configuracion-cupon="$configuracionCupon" />

                </div>

            </div>

        </div>

    </div>

    <div class="card">

        <div class="card-body">

            <h4 class="h6">Cupones generados: {{ $configuracionCupon->cupones()->count() }}</h4>

            @if ($configuracionCupon->tipo_cupon === \App\Models\ConfiguracionCupon::TIPO_PERSONALIZADO)
                <p class="text-muted small">Las instancias de este código se crean cuando un cliente lo aplica.</p>
            @endif

            <x-list.actions>

                <x-slot:search>

                    <x-list.search-input wire:model.live.debounce.1200ms="search" placeholder="Buscar cupón..." />

                </x-slot:search>

                <x-slot:group>

                    <div class="row justify-content-end">

                        <div class="col-12 col-md-5 col-lg-4">

                            <select class="form-select" wire:model.live="status" aria-label="Estado del cupón">

                                <option value="">Todos</option>
                                <option value="0">Disponible</option>
                                <option value="1">Redimido</option>

                            </select>

                        </div>

                    </div>

                </x-slot:group>

            </x-list.actions>

            <x-cupones.cupones-table :cupones="$cupones" :can-view-reservation="$canViewReservation" :sort-column="$sortColumn" :sort-direction="$sortDirection" />

            {{ $cupones->links() }}

        </div>

    </div>

    <x-layout.loader.fullpage wire:loading.delay.short />

</div>

@script
    <script>
        Alpine.data('detailCampana', () => ({
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


