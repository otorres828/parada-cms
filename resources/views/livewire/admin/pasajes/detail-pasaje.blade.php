{{--
    PASAJES — DETALLE
    --------------------------------------------------------------------------
    Presenta la fecha de reserva, la fecha y hora de salida de la programación, el viajero, su documento, asiento y estado de abordaje. Consulta el
    origen y destino comprados a través de la reserva y muestra el precio del boleto, el estado de
    pago y la tasa de servicio aplicada con su modalidad, valor y rango histórico.
    Muestra el QR generado localmente con el localizador únicamente si la reserva está pagada.

    Componentes reutilizables utilizados:
    - <x-form.cancel-button />: Enlace para regresar al listado anterior.
    - <x-layout.loader.fullpage />: Indicador global durante operaciones de Livewire.
    - <x-list.heading />: Cabecera del módulo con título y acciones.
    - <x-pasajes.description />: Ficha descriptiva del pasaje.
    --------------------------------------------------------------------------
--}}

@section('title', 'Pasajes')

<div x-data="detailPasaje" class="py-3">

    <x-list.heading>

        <x-slot:title>
            Pasajes @if ($pasaje_id)
                <small class="text-body-secondary">#{{ $pasaje_id }}</small>
            @endif

        </x-slot:title>

        <x-slot:button>

            <x-form.cancel-button :link="route('admin.pasajes.list')">
                Volver al listado
            </x-form.cancel-button>

        </x-slot:button>

    </x-list.heading>

    <div class="container-fluid px-0 mb-4">

        <div class="row g-3">

            <div class="col-md-6">

                <div class="card">

                    <x-pasajes.description :pasaje="$pasaje" :can-view-campaign="$canViewCampaign" :can-view-reservation="$canViewReservation" />

                </div>

            </div>

            @if ($qr)
            <div class="col-md-6">

                <div class="card">

                    <div class="card-header">Código QR del pasaje</div>

                    <div class="card-body text-center">

                            <img src="{{ $qr }}" alt="Código QR del pasaje #{{ $pasaje->id }}"
                                width="256" height="256" class="img-fluid bg-white">

                            <p class="small text-body-secondary text-break mt-3 mb-0">
                                {{ $pasaje->localizador }}
                            </p>

                    </div>

                </div>

            </div>

            @endif

        </div>

    </div>

    <x-layout.loader.fullpage wire:loading.delay.short />

</div>

@script
    <script>
        Alpine.data('detailPasaje', () => ({
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



