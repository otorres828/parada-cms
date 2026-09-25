{{--
    RESERVAS Y VENTAS — DETALLE
    --------------------------------------------------------------------------
    Presenta el comprador, la empresa, el origen y destino de la reserva y su estado de pago.
    Incluye el acceso a la programación según permisos y el desglose de viajeros, precios base,
    descuentos, precios finales, tasas de servicio y total individual de cada boleto.

    Componentes reutilizables utilizados:
    - <x-form.cancel-button />: Enlace para regresar al listado anterior.
    - <x-layout.loader.fullpage />: Indicador global durante operaciones de Livewire.
    - <x-list.heading />: Cabecera del módulo con título y acciones.
    - <x-reservas.description />: Ficha descriptiva de la reserva.
    - <x-reservas.pasajes-table />: Tabla de pasajes asociados a la reserva.
    --------------------------------------------------------------------------
--}}

@section('title', 'Reservas y ventas')

<div x-data="detailReserva" class="py-3">

    <x-list.heading>

        <x-slot:title>
            Reservas y ventas @if ($reserva_id)
                <small class="text-body-secondary">#{{ $reserva_id }}</small>
            @endif

        </x-slot:title>

        <x-slot:button>

            <x-form.cancel-button :link="route('admin.reservas.list')">
                Volver al listado
            </x-form.cancel-button>

        </x-slot:button>

    </x-list.heading>

    <div class="container-fluid px-0 mb-4">

        <div class="row">

            <div class="col-md-6">

                <div class="card">

                    <x-reservas.description :reserva="$reserva" :can-view-campaign="$canViewCampaign"
                        :can-view-passengers="$canViewPassengers" :can-view-reservation="$canViewReservation" />

                </div>

            </div>

        </div>

    </div>

    @php($tickets = $reserva->pasajes)

    <div class="card">

        <div class="card-header">
            Pasajeros
        </div>

        <div class="table-responsive">

            <x-reservas.pasajes-table :tickets="$tickets" :can-view-ticket="$canViewTicket" />

        </div>

    </div>

    <x-layout.loader.fullpage wire:loading.delay.short />

</div>

@script
    <script>
        Alpine.data('detailReserva', () => ({
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



