{{--
    RETIROS — DETALLE
    --------------------------------------------------------------------------
    Presenta la empresa, importe, datos bancarios, estado y resolución de la solicitud de retiro.

    Componentes reutilizables utilizados:
    - <x-list.heading />: Cabecera del módulo con título y acciones.
    - <x-form.cancel-button />: Enlace para regresar o cancelar la edición.
    - <x-list.status-badge />: Etiqueta visual del estado del registro.
    - <x-layout.loader.fullpage />: Indicador de carga durante las operaciones de Livewire.
    --------------------------------------------------------------------------
--}}

@section('title', 'Retiros')

<div x-data="detailRetiro" class="py-3">

    <x-list.heading>

        <x-slot:title>
            Retiros @if ($retiro_id)
                <small class="text-body-secondary">#{{ $retiro_id }}</small>

            @endif

        </x-slot:title>

        <x-slot:button>

            @if (\App\Services\Admin\Access::allows('retiros', 'list'))

                <x-form.cancel-button :link="route('admin.retiros.list')">
                    Volver al listado
                </x-form.cancel-button>

            @endif

        </x-slot:button>

    </x-list.heading>

    <div class="container-fluid px-0 mb-4">

        <div class="row col-12">

            <div class="col-md-6">

                <div class="card">

                    <div class="card-body">

                        <dl class="row mb-0">
                            <dt class="col-sm-4">Empresa</dt>
                            <dd class="col-sm-8">
                                {{ $retiro->empresa?->nombre ?? '—' }}
                            </dd>
                            <dt class="col-sm-4">Monto USD</dt>
                            <dd class="col-sm-8">
                                {{ number_format($retiro->monto ?? 0, 2) }}
                            </dd>
                            <dt class="col-sm-4">Estado</dt>
                            <dd class="col-sm-8">
                                <x-list.status-badge :status="$retiro->estatus" />
                            </dd>
                            <dt class="col-sm-4">Solicitado</dt>
                            <dd class="col-sm-8">
                                {{ $retiro->created_at?->format('d/m/Y H:i') ?? '—' }}
                            </dd>
                            <dt class="col-sm-4">Referencia</dt>
                            <dd class="col-sm-8">
                                {{ $retiro->referencia ?? '—' }}
                            </dd>
                            <dt class="col-sm-4">Datos bancarios</dt>
                            <dd class="col-sm-8">{{ $retiro->datos_bancarios ?? '—' }}</dd>
                            <dt class="col-sm-4">Observaciones</dt>
                            <dd class="col-sm-8">{{ $retiro->comentario ?? '—' }}</dd>
                            <dt class="col-sm-4">Fecha de resolución</dt>
                            <dd class="col-sm-8">{{ $retiro->fecha_resolucion?->format('d/m/Y H:i') ?? '—' }}</dd>
                        </dl>

                    </div>

                </div>

            </div>

        </div>

    </div>

    @if ($retiro->comprobante)

        <button class="btn btn-outline-primary mb-3" type="button" wire:click="downloadProof">Descargar comprobante</button>

    @endif

    <x-layout.loader.fullpage wire:loading.delay.short />

</div>

@script
    <script>
        Alpine.data('detailRetiro', () => ({
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
