@section('title', 'Terminales')

<div x-data="listTerminal" class="py-3">

    <x-list.heading>

        <x-slot:title>
            Terminales
        </x-slot:title>

        <x-slot:button>

            @if (Route::has('admin.terminales.add') && $canAdd)

                <x-list.add-button :route="route('admin.terminales.add')">
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

            <label class="form-label" for="listTerminal-status">
                Estado
            </label>

            <select id="listTerminal-status" class="form-select" wire:model.live="status">

                <option value="">Todos</option>
                <option value="1">Activo</option>
                <option value="0">Inactivo</option>

            </select>

        </div>

    </div>

    <x-list.table>

        <thead>

            <tr>
                <th>ID
                    <x-list.sortable-button column="id" :$sortColumn :$sortDirection />
                </th>

                <th>Terminal
                    <x-list.sortable-button column="nombre" :$sortColumn :$sortDirection />
                </th>

                <th>Estado </th>

                <th>Dirección
                    <x-list.sortable-button column="direccion" :$sortColumn :$sortDirection />
                </th>

                <th>Estado
                    <x-list.sortable-button column="estatus" :$sortColumn :$sortDirection />
                </th>

                <th class="text-end">Acciones</th>

            </tr>
        </thead>

        <tbody>

            @forelse ($terminales as $terminal)

                <tr wire:key="listTerminal-{{ $terminal->id }}">
                    <td>
                        {{ $terminal->id }}
                    </td>

                    <td>
                        {{ $terminal->nombre ?? '—' }}
                    </td>

                    <td>
                        {{ $terminal->estado?->nombre ?? '—' }}
                    </td>

                    <td>
                        {{ $terminal->direccion ?? '—' }}
                    </td>

                    <td>
                        <x-list.status-badge :status="$terminal->estatus" />
                    </td>

                    <td class="text-end">

                        <x-list.button-group>

                            @if ($capabilities['detail'])

                                <x-list.view-button :route="route('admin.terminales.detail', ['terminal_id' => $terminal->id])" :target="false" />

                            @endif

                            @if ($capabilities['edit'])

                                <x-list.edit-button :route="route('admin.terminales.edit', ['terminal_id' => $terminal->id])" />

                            @endif

                            @if ($canEdit)

                                <x-list.status-button wire:click="changeStatus({{ $terminal->id }})" :status="$terminal->estatus"
                                    wire:loading.attr="disabled" />

                            @endif

                        </x-list.button-group>

                    </td>

                </tr>

            @empty

                <tr>
                    <td colspan="6" class="text-center py-5">
                        No se encontraron registros.
                    </td>

                </tr>

            @endforelse

        </tbody>

    </x-list.table>

    {{ $terminales->links() }}

    <x-layout.loader.fullpage wire:loading.delay.short />

</div>

@script
    <script>
        Alpine.data('listTerminal', () => ({
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
