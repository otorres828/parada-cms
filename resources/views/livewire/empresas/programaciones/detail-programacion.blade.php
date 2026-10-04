{{--
    PROGRAMACIONES — PASAJEROS Y TARIFAS POR TRAMO
    --------------------------------------------------------------------------
    Coordina el detalle de la programación mediante componentes de presentación.
    <x-form.cancel-button /> permite regresar al listado según los permisos del usuario.
    --------------------------------------------------------------------------
--}}

@section('title', 'Programaciones')

<div x-data="DetailProgramacion" class="py-3">

    <x-list.heading>

        <x-slot:title>

            Programaciones @if ($programacion_id)
                <small class="text-body-secondary">#{{ $programacion_id }}</small>
            @endif

        </x-slot:title>

        @if ($canList)
            <x-slot:button>

                <x-form.cancel-button :link="route('empresas.programaciones.list')">
                    Volver al listado
                </x-form.cancel-button>

            </x-slot:button>
        @endif

    </x-list.heading>

    <div class="container-fluid px-0 mb-4">

        <div class="row g-3">

            <div class="col-md-5">

                <x-programacion.description 
                    :programacion="$programacion" 
                    :capacidad="$capacidad"
                    :pasajes-pagados="$pasajesPagados" 
                    :pasajes-pendientes="$pasajesPendientes"
                    :can-viajes-detail="$canViajesDetail" 
                    :can-transportes-detail="$canTransportesDetail" 
                    :view-tasa-servicio="$viewTasaServicio"
                    :show-empresa="false"
                    route-viaje="empresas.viajes.detail"
                />

            </div>

            <div class="col-md-7">

                <x-programacion.rates-matrix 
                    :programacion="$programacion"
                    :disponibilidad-tramos="$disponibilidadTramos" 
                    :tipo-cambio="$tipoCambioVigente" 
                />

            </div>
        </div>
    </div>

    <x-programacion.passengers-table 
        :tickets="$tickets" 
        :can-reservas-detail="$canReservasDetail" 
        :view-tasa-servicio="$viewTasaServicio"
    />

    <x-layout.loader.fullpage wire:loading.delay.short />

</div>

@script
    <script>
        Alpine.data('DetailProgramacion', () => ({
            search: '',
            normalize(value) {
                return String(value ?? '').normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase();
            },
            matches(value) {
                return this.normalize(value).includes(this.normalize(this.search.trim()));
            },
            hasMatches() {
                return [...this.$root.querySelectorAll('[data-search]')].some(row => this.matches(row.dataset
                    .search));
            },
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
