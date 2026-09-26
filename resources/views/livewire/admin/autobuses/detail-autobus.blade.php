{{--
    AUTOBUSES — DETALLE
    --------------------------------------------------------------------------
    Presenta la empresa, placa, modelo, capacidad y estado del autobús. Incluye la tabla de
    programaciones consultadas, con el precio de la primera tarifa asociada, boletos pagados y
    pendientes, ventas y tasas de servicio.

    Componentes reutilizables utilizados:
    - <x-autobuses.description />: Ficha descriptiva del autobús.
    - <x-autobuses.programaciones-table />: Historial de programaciones del autobús.
    - <x-form.cancel-button />: Enlace para regresar al listado anterior.
    - <x-layout.loader.fullpage />: Indicador global durante operaciones de Livewire.
    - <x-list.heading />: Cabecera del módulo con título y acciones.
    --------------------------------------------------------------------------
--}}

@section('title', 'Autobuses')

<div x-data="detailAutobus" class="py-3">

    <x-list.heading>

        <x-slot:title>
            Programaciones del autobus @if ($autobus_id)
                <small class="text-body-secondary">#{{ $autobus_id }}</small>
            @endif

        </x-slot:title>

        <x-slot:button>

            <x-form.cancel-button :link="route('admin.autobuses.list')">
                Volver al listado
            </x-form.cancel-button>

        </x-slot:button>

    </x-list.heading>

    <div class="container-fluid px-0 mb-4">

        <div class="row">

            <div class="col-md-6">

                <div class="card">

                    <x-autobuses.description :autobus="$autobus" />

                </div>

            </div>

        </div>

    </div>

    <div class="card mt-4">

        <div class="card-header">
            Historial de programaciones
        </div>

        <div class="table-responsive">

            <x-autobuses.programaciones-table :programaciones="$programaciones" :can-view-passengers="$canViewPassengers"
                :tipo-cambio="$tipoCambioVigente" />

            {{ $programaciones->links() }}

        </div>

    </div>

    <x-layout.loader.fullpage wire:loading.delay.short />

</div>

@script
    <script>
        Alpine.data('detailAutobus', () => ({
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




