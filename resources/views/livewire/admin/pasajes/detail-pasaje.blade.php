@section('title', 'Pasajes')

<div x-data="detailPasaje" class="py-3">

    <x-list.heading>

        <x-slot:title>
            Pasajes @if ($pasaje_id)
                <small class="text-body-secondary">#{{ $pasaje_id }}</small>
            @endif

        </x-slot:title>

        <x-slot:button>

            @if (\App\Services\Admin\Access::allows('pasajes', 'list'))
                <x-form.cancel-button :link="route('admin.pasajes.list')">
                    Volver al listado
                </x-form.cancel-button>
            @endif

        </x-slot:button>

    </x-list.heading>

    @if ($errors->any())

        <div class="alert alert-danger" role="alert">

            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>

        </div>

    @endif

    <div class="container-fluid px-0 mb-4">

        <div class="row">

            <div class="col-md-6">

                <div class="card">

                    <div class="card-body">

                        <dl class="row mb-0">
                            <dt class="col-sm-4">Reserva</dt>
                            <dd class="col-sm-8">
                                {{ $pasaje->reserva?->codigo_referencia ?? '—' }}</dd>
                            <dt class="col-sm-4">Viajero</dt>
                            <dd class="col-sm-8">{{ $pasaje->viajero?->nombre ?? '—' }}</dd>
                            <dt class="col-sm-4">Documento</dt>
                            <dd class="col-sm-8">
                                {{ $pasaje->viajero?->documento_identidad ?? '—' }}</dd>
                            <dt class="col-sm-4">Asiento</dt>
                            <dd class="col-sm-8">{{ $pasaje->numero_asiento ?? '—' }}</dd>
                            <dt class="col-sm-4">Precio USD</dt>
                            <dd class="col-sm-8">{{ number_format($pasaje->precio_final ?? 0, 2) }}</dd>
                            <dt class="col-sm-4">Abordado</dt>
                            <dd class="col-sm-8">{{ $pasaje->abordado ? 'Sí' : 'No' }}</dd>
                            <dt class="col-sm-4">Pago</dt>
                            <dd class="col-sm-8"><x-list.status-badge :status="$pasaje->reserva?->estado_pago" /></dd>
                            <dt class="col-sm-4">Tasa de servicio USD</dt>
                            <dd class="col-sm-8">{{ number_format($pasaje->tasa_servicio, 2) }}</dd>
                            <dt class="col-sm-4">Tipo de tasa aplicada</dt>
                            <dd class="col-sm-8">
                                {{ $pasaje->tipo_servicio === 2 ? 'Porcentaje' : ($pasaje->tipo_servicio === 1 ? 'Monto fijo' : 'Registro histórico') }}
                            </dd>
                            <dt class="col-sm-4">Valor aplicado</dt>
                            <dd class="col-sm-8">
                                {{ $pasaje->valor_servicio !== null ? number_format($pasaje->valor_servicio, 2) . ($pasaje->tipo_servicio === 2 ? ' %' : ' USD') : 'No registrado' }}
                            </dd>
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
            async generateCoupons() {
                const result = await Swal.fire({
                    title: '¿Generar los cupones de esta campaña?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Generar',
                    cancelButtonText: 'Cancelar',
                });
                if (result.isConfirmed) await $wire.call('generateCoupons');
            },
        }));
    </script>
@endscript
