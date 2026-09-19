{{--
    MOVIMIENTOS CONTABLES — DETALLE
    --------------------------------------------------------------------------
    Muestra la empresa, tipo, importe, descripción y fecha del movimiento financiero seleccionado.

    Componentes reutilizables utilizados:
    - <x-list.heading />: Cabecera del módulo con título y acciones.
    - <x-form.cancel-button />: Enlace para regresar o cancelar la edición.
    - <x-layout.loader.fullpage />: Indicador de carga durante las operaciones de Livewire.
    --------------------------------------------------------------------------
--}}

@section('title', 'Movimientos contables')

<div x-data="detailMovimiento" class="py-3">

    <x-list.heading>

        <x-slot:title>
            Movimientos contables @if ($movimiento_id)
                <small class="text-body-secondary">#{{ $movimiento_id }}</small>
            @endif

        </x-slot:title>

        <x-slot:button>

            @if (\App\Services\Admin\Access::allows('movimientos', 'list'))
                <x-form.cancel-button :link="route('admin.movimientos.list')">
                    Volver al listado
                </x-form.cancel-button>
            @endif

        </x-slot:button>

    </x-list.heading>

    <div class="container-fluid px-0 mb-4">

        <div class="row">

            <div class="col-md-6">

                <div class="card">

                    <div class="card-body">

                        <dl class="row mb-0">
                            <dt class="col-sm-4">Empresa</dt>
                            <dd class="col-sm-8">{{ $movimiento->empresa?->nombre ?? '—' }}</dd>
                            <dt class="col-sm-4">Concepto</dt>
                            <dd class="col-sm-8">{{ $movimiento->tipo ?? '—' }}</dd>
                            <dt class="col-sm-4">Importe USD</dt>
                            <dd class="col-sm-8">{{ number_format($movimiento->monto ?? 0, 2) }}</dd>
                            <dt class="col-sm-4">Descripción</dt>
                            <dd class="col-sm-8">{{ $movimiento->descripcion ?? '—' }}</dd>
                            <dt class="col-sm-4">Fecha</dt>
                            <dd class="col-sm-8">{{ $movimiento->created_at?->format('d/m/Y H:i') ?? '—' }}</dd>
                        </dl>

                    </div>

                </div>

            </div>

        </div>

    </div>

    <x-layout.loader.fullpage wire:loading.delay.short />

</div>

@script
    <script>
        Alpine.data('detailMovimiento', () => ({
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
