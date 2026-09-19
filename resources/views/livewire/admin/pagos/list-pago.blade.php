@section('title', 'Pagos recibidos')

<div x-data="listPago" class="py-3">

    <x-list.heading>

        <x-slot:title>
            Pagos recibidos
        </x-slot:title>

        <x-slot:button>
            <button type="button" class="btn btn-outline-success" wire:click="export" wire:loading.attr="disabled"><i
                    class="bi bi-file-earmark-excel me-1"></i>Descargar Excel</button>
            @if (Route::has('admin.pagos.add') && $canAdd)
                <x-list.add-button :route="route('admin.pagos.add')">
                    Nuevo registro
                </x-list.add-button>
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

    <x-list.actions>

        <x-slot:search>

            <x-list.search-input wire:model.live.debounce.1200ms="search" />

        </x-slot:search>

        <x-slot:group>

        </x-slot:group>

    </x-list.actions>

    <div class="row g-3 mb-3">

        <div class="col-md-3">

            <label class="form-label" for="filtro-empresa">
                Empresa
            </label>

            <select id="filtro-empresa" class="form-select" wire:model.live="empresa_id">

                <option value="">Todas las empresas</option>
                @foreach ($empresas as $empresa)
                    <option value="{{ $empresa->id }}">{{ $empresa->nombre }}</option>
                @endforeach

            </select>

        </div>

        <div class="col-md-3">

            <label class="form-label" for="listPago-from">
                Desde
            </label>
            <input id="listPago-from" type="date" class="form-control" wire:model.live="date_from">
        </div>

        <div class="col-md-3">

            <label class="form-label" for="listPago-to">
                Hasta
            </label>
            <input id="listPago-to" type="date" class="form-control" wire:model.live="date_to">
        </div>

    </div>

    <p class="text-body-secondary">Conciliación de pagos recibidos. Registrar el pago acredita el neto de la empresa.</p>

    <x-list.table>

        <thead>

            <tr>
                <th>ID
                    <x-list.sortable-button column="id" :$sortColumn :$sortDirection />
                </th>
                <th>Referencia
                    <x-list.sortable-button column="referencia" :$sortColumn :$sortDirection />
                </th>
                <th>Reserva </th>
                <th>Empresa </th>
                <th>Recibido USD
                    <x-list.sortable-button column="monto" :$sortColumn :$sortDirection />
                </th>
                <th>Neto empresa USD
                    <x-list.sortable-button column="neto_empresa" :$sortColumn :$sortDirection />
                </th>
                <th>Fecha
                    <x-list.sortable-button column="fecha_pago" :$sortColumn :$sortDirection />
                </th>
                <th class="text-end">Acciones</th>
            </tr>
        </thead>

        <tbody>
            @forelse ($pagos as $pago)

                <tr wire:key="listPago-{{ $pago->id }}">
                    <td>{{ $pago->id }}</td>
                    <td>{{ $pago->referencia ?? '—' }}</td>
                    <td>{{ $pago->reserva?->codigo_referencia ?? '—' }}</td>
                    <td>{{ $pago->empresa?->nombre ?? '—' }}</td>
                    <td>{{ number_format($pago->monto ?? 0, 2) }}</td>
                    <td>{{ number_format($pago->neto_empresa ?? 0, 2) }}</td>
                    <td>{{ $pago->fecha_pago?->format('d/m/Y H:i') ?? '—' }}</td>
                    <td class="text-end">

                        <x-list.button-group>

                            @if ($capabilities['detail'])
                                <x-list.view-button :route="route('admin.pagos.detail', ['pago_id' => $pago->id])" :target="false" />
                            @endif

                        </x-list.button-group>

                    </td>
                </tr>
            @empty

                <tr>
                    <td colspan="8" class="text-center py-5">No se encontraron registros.</td>
                </tr>
            @endforelse
        </tbody>

    </x-list.table>

    {{ $pagos->links() }}

    <x-layout.loader.fullpage wire:loading.delay.short />

</div>

@script
    <script>
        Alpine.data('listPago', () => ({
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
