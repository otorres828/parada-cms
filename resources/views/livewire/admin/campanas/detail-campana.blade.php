@section('title', 'Campañas')

<div x-data="detailCampana" class="py-3">

    <x-list.heading>

        <x-slot:title>
            Campañas @if ($configuracion_cupon_id)
                <small class="text-body-secondary">#{{ $configuracion_cupon_id }}</small>
            @endif

        </x-slot:title>

        <x-slot:button>

            @if (\App\Services\Admin\Access::allows('campanas', 'list'))
                <x-form.cancel-button :link="route('admin.campanas.list')">
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
                            <dt class="col-sm-4">Campaña</dt>
                            <dd class="col-sm-8">
                                {{ $configuracionCupon->nombre_campana ?? '—' }}
                            </dd>
                            <dt class="col-sm-4">Empresa</dt>
                            <dd class="col-sm-8">
                                {{ $configuracionCupon->empresa?->nombre ?? '—' }}
                            </dd>
                            <dt class="col-sm-4">Descuento</dt>
                            <dd class="col-sm-8">
                                {{ $configuracionCupon->tipo_descuento ?? '—' }}
                            </dd>
                            <dt class="col-sm-4">Valor</dt>
                            <dd class="col-sm-8">
                                {{ number_format($configuracionCupon->monto_descuento ?? 0, 2) }}
                            </dd>
                            <dt class="col-sm-4">Vencimiento</dt>
                            <dd class="col-sm-8">
                                {{ $configuracionCupon->fecha_fin?->format('d/m/Y H:i') ?? '—' }}
                            </dd>
                            <dt class="col-sm-4">Estado</dt>
                            <dd class="col-sm-8">
                                <x-list.status-badge :status="$configuracionCupon->estatus" />
                            </dd>
                        </dl>

                    </div>

                </div>

            </div>

        </div>

    </div>

    <div class="card">

        <div class="card-body">

            <h4 class="h6">Cupones generados: {{ $configuracionCupon->cupones()->count() }}</h4>
            @if (!$configuracionCupon->cupones()->exists() && \App\Services\Admin\Access::allows('campanas', 'edit'))
                <button class="btn btn-primary" type="button" @click="generateCoupons" wire:loading.attr="disabled">Generar
                    cupones</button>
            @endif

            <x-list.actions>

                <x-slot:search>

                    <x-list.search-input wire:model.live.debounce.1200ms="search" placeholder="Buscar cupón..." />

                </x-slot:search>

                <x-slot:group>

                    <select class="form-select" wire:model.live="status" aria-label="Estado del cupón">

                        <option value="">Todos</option>
                        <option value="0">Disponible</option>
                        <option value="1">Redimido</option>

                    </select>

                </x-slot:group>

            </x-list.actions>

            <x-list.table>

                <thead>

                    <tr>
                        <th>Código
                            <x-list.sortable-button column="codigo" :$sortColumn :$sortDirection />
                        </th>
                        <th>Estado</th>
                        <th>Cliente</th>
                        <th>Fecha de redención</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($cupones as $cupon)

                        <tr wire:key="cupon-{{ $cupon->id }}">
                            <td>{{ $cupon->codigo }}</td>
                            <td>{{ $cupon->redimido ? 'Redimido' : 'Disponible' }}</td>
                            <td>{{ $cupon->usuario?->name ?? '—' }}</td>
                            <td>{{ $cupon->fecha_redencion?->format('d/m/Y H:i') ?? '—' }}</td>
                        </tr>
                    @empty

                        <tr>
                            <td colspan="4" class="text-center py-4">No se encontraron cupones.</td>
                        </tr>
                    @endforelse
                </tbody>

            </x-list.table>

            {{ $cupones->links() }}

        </div>

    </div>

    <x-layout.loader.fullpage wire:loading.delay.short />

</div>

@script
    <script>
        Alpine.data('detailCampana', () => ({
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
