{{--
    REEMBOLSOS — DETALLE
    --------------------------------------------------------------------------
    Presenta el importe, motivo, estado y datos de resolución de un reembolso, vinculados a la
    empresa y al pago original.

    Componentes reutilizables utilizados:
    - <x-form.cancel-button />: Enlace para regresar al listado anterior.
    - <x-layout.loader.fullpage />: Indicador global durante operaciones de Livewire.
    - <x-list.heading />: Cabecera del módulo con título y acciones.
    - <x-reembolsos.description />: Ficha descriptiva del reembolso.
    --------------------------------------------------------------------------
--}}

@section('title', 'Reembolsos')

<div x-data="detailReembolso" class="py-3">

    <x-list.heading>

        <x-slot:title>
            Reembolsos @if ($reembolso_id)
                <small class="text-body-secondary">#{{ $reembolso_id }}</small>
            @endif

        </x-slot:title>

        <x-slot:button>

            <x-form.cancel-button :link="route('empresas.reembolsos.list')">
                Volver al listado
            </x-form.cancel-button>

        </x-slot:button>

    </x-list.heading>

    <div class="container-fluid px-0 mb-4">

        <div class="row">

            <div class="col-md-6">

                <div class="card">

                    <x-reembolsos.description :reembolso="$reembolso" />

                </div>

            </div>

        </div>

    </div>

    @if ($reembolso->comprobante)
        <button class="btn btn-outline-primary mb-3" type="button" wire:click="downloadProof">Descargar
            comprobante</button>
    @endif

    <x-layout.loader.fullpage wire:loading.delay.short />

</div>

@script
    <script>
        Alpine.data('detailReembolso', () => ({
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



