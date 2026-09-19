@section('title', 'Campañas')

<div x-data="listCampana" class="py-3">

    <x-list.heading>

        <x-slot:title>
            Campañas
        </x-slot:title>

        <x-slot:button>

            @if (Route::has('admin.campanas.add') && $canAdd)
                <x-list.add-button :route="route('admin.campanas.add')">
                    Nuevo registro
                </x-list.add-button>
            @endif

        </x-slot:button>

    </x-list.heading>



    <x-list.actions>

        <x-slot:search>

            <x-list.search-input wire:model.live.debounce.1200ms="search" />

        </x-slot:search>

        <x-slot:group>

        </x-slot:group>

    </x-list.actions>

    <div class="row g-3 mb-3">

        <div class="col-md-3">

            <label class="form-label" for="listCampana-status">
                Estado
            </label>

            <select id="listCampana-status" class="form-select" wire:model.live="status">

                <option value="">Todos</option>
                <option value="1">Activo</option>
                <option value="0">Inactivo</option>

            </select>

        </div>

        <div class="col-md-3">

            <label class="form-label" for="listCampana-from">
                Desde
            </label>
            <input id="listCampana-from" type="date" class="form-control" wire:model.live="date_from">
        </div>

        <div class="col-md-3">

            <label class="form-label" for="listCampana-to">
                Hasta
            </label>
            <input id="listCampana-to" type="date" class="form-control" wire:model.live="date_to">
        </div>

    </div>

    <x-list.table>

        <thead>

            <tr>
                <th>ID
                    <x-list.sortable-button column="id" :$sortColumn :$sortDirection />
                </th>
                <th>Campaña
                    <x-list.sortable-button column="nombre_campana" :$sortColumn :$sortDirection />
                </th>
                <th>Empresa </th>
                <th>Descuento
                    <x-list.sortable-button column="tipo_descuento" :$sortColumn :$sortDirection />
                </th>
                <th>Valor
                    <x-list.sortable-button column="monto_descuento" :$sortColumn :$sortDirection />
                </th>
                <th>Vencimiento
                    <x-list.sortable-button column="fecha_fin" :$sortColumn :$sortDirection />
                </th>
                <th>Estado
                    <x-list.sortable-button column="estatus" :$sortColumn :$sortDirection />
                </th>
                <th class="text-end">Acciones</th>
            </tr>
        </thead>

        <tbody>
            @forelse ($campanas as $configuracionCupon)

                <tr wire:key="listCampana-{{ $configuracionCupon->id }}">
                    <td>{{ $configuracionCupon->id }}</td>
                    <td>{{ $configuracionCupon->nombre_campana ?? '—' }}</td>
                    <td>{{ $configuracionCupon->empresa?->nombre ?? '—' }}</td>
                    <td>{{ $configuracionCupon->tipo_descuento ?? '—' }}</td>
                    <td>{{ number_format($configuracionCupon->monto_descuento ?? 0, 2) }}</td>
                    <td>{{ $configuracionCupon->fecha_fin?->format('d/m/Y H:i') ?? '—' }}</td>
                    <td>
                        <x-list.status-badge :status="$configuracionCupon->estatus" />
                    </td>
                    <td class="text-end">

                        <x-list.button-group>

                            @if ($capabilities['detail'])
                                <x-list.view-button :route="route('admin.campanas.detail', ['configuracion_cupon_id' => $configuracionCupon->id])" :target="false" />
                            @endif
                            @if ($capabilities['edit'])
                                <x-list.edit-button :route="route('admin.campanas.edit', ['configuracion_cupon_id' => $configuracionCupon->id])" />
                            @endif
                            @if ($canEdit)
                                <x-list.status-button wire:click="changeStatus({{ $configuracionCupon->id }})" :status="$configuracionCupon->estatus"
                                    wire:loading.attr="disabled" />
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

    {{ $campanas->links() }}

    <x-layout.loader.fullpage wire:loading.delay.short />

</div>

@script
    <script>
        Alpine.data('listCampana', () => ({
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
