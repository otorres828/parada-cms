{{--
    TRANSPORTES — DETALLE
    --------------------------------------------------------------------------
    Presenta la empresa, placa, modelo, capacidad y estado del transporte. Incluye la tabla de
    programaciones consultadas, con el precio de la primera tarifa asociada, boletos pagados y
    pendientes, ventas y tasas de servicio.

    Componentes reutilizables utilizados:
    - <x-transportes.description />: Ficha descriptiva del transporte.
    - <x-empresas.transportes.programaciones-table />: Historial de programaciones del transporte.
    - <x-form.cancel-button />: Enlace para regresar al listado anterior.
    - <x-layout.loader.fullpage />: Indicador global durante operaciones de Livewire.
    - <x-list.heading />: Cabecera del módulo con título y acciones.
    --------------------------------------------------------------------------
--}}

@section('title', 'Transportes')

<div x-data="detailTransporte" class="py-3">

    <x-list.heading>

        <x-slot:title>
            Programaciones del transporte @if ($transporte_id)
                <small class="text-body-secondary">#{{ $transporte_id }}</small>
            @endif

            <span class="badge text-bg-secondary ms-2">{{ $transporte->getTipoTransporte() }}</span>

        </x-slot:title>

        <x-slot:button>

            <x-form.cancel-button :link="route('empresas.transportes.list')" >
                Volver al listado
            </x-form.cancel-button>

        </x-slot:button>

    </x-list.heading>

    <div class="container-fluid px-0 mb-4">

        <div class="row">

            <div class="col-md-6">

                <div class="card">

                    <x-transportes.description
                        :transporte="$transporte"
                        :show-empresa="false"
                    />

                </div>

            </div>

        </div>

    </div>

    <div class="card mt-4">

        <div class="card-header">

            Historial de programaciones

        </div>

        <div class="table-responsive">

            <x-transportes.programaciones-table
                :programaciones="$programaciones"
                :can-view-passengers="$canViewPassengers"
                :tipo-cambio="$tipoCambioVigente"
                :view-tasa-servicio="$viewTasaServicio"
                route-programacion="empresas.programaciones.detail"
            />

            {{ $programaciones->links() }}

        </div>

    </div>

    <x-layout.loader.fullpage wire:loading.delay.short />

</div>

@script
    <script>
        Alpine.data('detailTransporte', () => ({
            destroy() {
                this.toastCleanup?.forEach(cleanup => cleanup());
            },
            init() {
                this.toastCleanup = [
                    Livewire.on('empresas_transporte_success', data => this.$store.toast.success(data.message)),
                    Livewire.on('empresas_transporte_error', data => this.$store.toast.info(data.message)),
                ];
            },

        }));
    </script>
@endscript
