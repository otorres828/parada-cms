@section('title', 'Reembolsos')

<div x-data="listReembolso" class="py-3">

    <x-list.heading>

        <x-slot:title>
            Reembolsos
        </x-slot:title>

        <x-slot:button>

            @if (Route::has('admin.reembolsos.add') && $canAdd)
                <x-list.add-button :route="route('admin.reembolsos.add')">
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

            <label class="form-label" for="listReembolso-status">
                Estado
            </label>

            <select id="listReembolso-status" class="form-select" wire:model.live="status">

                <option value="">Todos</option>
                <option value="pendiente">Pendiente</option>
                <option value="aprobado">Aprobado</option>
                <option value="pagado">Reembolsado</option>
                <option value="rechazado">Rechazado</option>

            </select>

        </div>

        <div class="col-md-3">

            <label class="form-label" for="listReembolso-from">
                Desde
            </label>
            <input id="listReembolso-from" type="date" class="form-control" wire:model.live="date_from">
        </div>

        <div class="col-md-3">

            <label class="form-label" for="listReembolso-to">
                Hasta
            </label>
            <input id="listReembolso-to" type="date" class="form-control" wire:model.live="date_to">
        </div>

    </div>

    <x-list.table>

        <thead>

            <tr>
                <th>ID
                    <x-list.sortable-button column="id" :$sortColumn :$sortDirection />
                </th>
                <th>Pago </th>
                <th>Empresa </th>
                <th>Monto USD
                    <x-list.sortable-button column="monto" :$sortColumn :$sortDirection />
                </th>
                <th>Estado
                    <x-list.sortable-button column="estatus" :$sortColumn :$sortDirection />
                </th>
                <th>Solicitado
                    <x-list.sortable-button column="created_at" :$sortColumn :$sortDirection />
                </th>
                <th class="text-end">Acciones</th>
            </tr>
        </thead>

        <tbody>
            @forelse ($reembolsos as $reembolso)

                <tr wire:key="listReembolso-{{ $reembolso->id }}">
                    <td>{{ $reembolso->id }}</td>
                    <td>{{ $reembolso->pago?->referencia ?? '—' }}</td>
                    <td>{{ $reembolso->empresa?->nombre ?? '—' }}</td>
                    <td>{{ number_format($reembolso->monto ?? 0, 2) }}</td>
                    <td>
                        <x-list.status-badge :status="$reembolso->estatus" />
                    </td>
                    <td>{{ $reembolso->created_at?->format('d/m/Y H:i') ?? '—' }}</td>
                    <td class="text-end">

                        <x-list.button-group>

                            @if ($capabilities['detail'])
                                <x-list.view-button :route="route('admin.reembolsos.detail', ['reembolso_id' => $reembolso->id])" :target="false" />
                            @endif
                            @if ($capabilities['review'])
                                <a class="btn btn-outline-secondary"
                                    href="{{ route('admin.reembolsos.review', ['reembolso_id' => $reembolso->id]) }}" wire:navigate
                                    title="Revisar" aria-label="Revisar"><i class="bi bi-clipboard-check-fill"></i></a>
                            @endif

                        </x-list.button-group>

                    </td>
                </tr>
            @empty

                <tr>
                    <td colspan="7" class="text-center py-5">No se encontraron registros.</td>
                </tr>
            @endforelse
        </tbody>

    </x-list.table>

    {{ $reembolsos->links() }}

    <x-layout.loader.fullpage wire:loading.delay.short />

</div>

@script
    <script>
        Alpine.data('listReembolso', () => ({
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
