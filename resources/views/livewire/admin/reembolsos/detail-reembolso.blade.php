@section('title', 'Reembolsos')

<div x-data="detailReembolso" class="py-3">

    <x-list.heading>

        <x-slot:title>
            Reembolsos @if ($reembolso_id)
                <small class="text-body-secondary">#{{ $reembolso_id }}</small>
            @endif

        </x-slot:title>

        <x-slot:button>

            @if (\App\Services\Admin\Access::allows('reembolsos', 'list'))
                <x-form.cancel-button :link="route('admin.reembolsos.list')">
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
                            <dt class="col-sm-4">Pago</dt>
                            <dd class="col-sm-8">
                                {{ $reembolso->pago?->referencia ?? '—' }}
                            </dd>
                            <dt class="col-sm-4">Empresa</dt>
                            <dd class="col-sm-8">
                                {{ $reembolso->empresa?->nombre ?? '—' }}
                            </dd>
                            <dt class="col-sm-4">Monto USD</dt>
                            <dd class="col-sm-8">
                                {{ number_format($reembolso->monto ?? 0, 2) }}
                            </dd>
                            <dt class="col-sm-4">Estado</dt>
                            <dd class="col-sm-8">
                                <x-list.status-badge :status="$reembolso->estatus" />
                            </dd>
                            <dt class="col-sm-4">Solicitado</dt>
                            <dd class="col-sm-8">
                                {{ $reembolso->created_at?->format('d/m/Y H:i') ?? '—' }}
                            </dd>
                            <dt class="col-sm-4">Motivo</dt>
                            <dd class="col-sm-8">{{ $reembolso->motivo ?? '—' }}</dd>
                            <dt class="col-sm-4">Observaciones</dt>
                            <dd class="col-sm-8">{{ $reembolso->comentario ?? '—' }}</dd>
                            <dt class="col-sm-4">Fecha de resolución</dt>
                            <dd class="col-sm-8">{{ $reembolso->fecha_resolucion?->format('d/m/Y H:i') ?? '—' }}</dd>
                        </dl>

                    </div>

                </div>

            </div>

        </div>

    </div>

    @if ($reembolso->comprobante)
        <button class="btn btn-outline-primary mb-3" type="button" wire:click="downloadProof">Descargar comprobante</button>
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
