{{--
    TRANSPORTES — LISTADO
    --------------------------------------------------------------------------
    Permite consultar los transportes de las empresas. Incluye búsqueda, ordenación y paginación.
    Ofrece los filtros disponibles en la pantalla. Presenta las acciones de cada registro según
    las autorizaciones del usuario empresarial.

    Componentes reutilizables utilizados:
    - <x-list.heading />: Elemento de presentación del listado.
    - <x-slot />: Elemento de presentación del listado.
    - <x-list.actions />: Elemento de presentación del listado.
    - <x-list.search-input />: Elemento de presentación del listado.
    - <x-list.table />: Tabla propia del panel.
    - <x-list.sortable-button />: Ordenación de columnas.
    - <x-list.status-badge />: Elemento de presentación del listado.
    - <x-list.edit-button />: Acceso a edición.
    - <x-list.status-button />: Activa e inactiva el transporte.
    - <x-list.button-group />: Elemento de presentación del listado.
    - <x-layout.loader.fullpage />: Elemento de presentación del listado.
    --------------------------------------------------------------------------
--}}

@section('title', 'Transportes')

<div x-data="listTransporte" class="py-3">

    <x-list.heading>

        <x-slot:title>
            Transportes
        </x-slot:title>

    </x-list.heading>

    <x-list.actions>

        <x-slot:search>

            <x-list.search-input wire:model.live.debounce.1200ms="search" />

        </x-slot:search>

        <x-slot:group>

            <select id="listTransporte-status" class="form-select" style="width: 220px; max-width: 100%;" aria-label="Estado" wire:model.live="status">

                <option value="">Todos</option>
                <option value="1">Activo</option>
                <option value="2">Inactivo</option>

            </select>

        </x-slot:group>

        @if ($canAdd)
            <x-slot:button>
                <x-list.add-button :route="route('empresas.transportes.add')">Nuevo registro</x-list.add-button>
            </x-slot:button>
        @endif

    </x-list.actions>

    <x-list.table>

        <thead>

            <tr>

                <th>ID
                    <x-list.sortable-button column="id" :$sortColumn :$sortDirection />
                </th>


                <th>Tipo de transporte</th>
                <th>Placa
                    <x-list.sortable-button column="placa" :$sortColumn :$sortDirection />
                </th>

                <th>Modelo
                    <x-list.sortable-button column="modelo" :$sortColumn :$sortDirection />
                </th>

                <th>Asientos
                    <x-list.sortable-button column="total_asientos" :$sortColumn :$sortDirection />
                </th>

                <th>Estado
                    <x-list.sortable-button column="estatus" :$sortColumn :$sortDirection />
                </th>

                <th></th>

            </tr>

        </thead>

        <tbody>

            @forelse ($transportes as $transporte)
                <tr wire:key="listTransporte-{{ $transporte->id }}">

                    <td>
                        {{ $transporte->id }}
                    </td>


                    <td>{{ $transporte->getTipoTransporte() }}</td>
                    <td>
                        {{ $transporte->placa ?? '—' }}
                    </td>

                    <td>
                        {{ $transporte->modelo ?? '—' }}
                    </td>

                    <td>
                        {{ $transporte->total_asientos ?? '—' }}
                    </td>

                    <td>
                        <x-list.status-badge :status="$transporte->estatus" />
                    </td>

                    <td class="text-end">

                        <x-list.button-group>

                            @if ($canEdit && ! $transporte->es_plantilla)
                                <x-list.edit-button :route="route('empresas.transportes.edit', ['transporte_id' => $transporte->id])" :target="false" />
                                <x-list.status-button wire:click="changeStatus({{ $transporte->id }})" :status="$transporte->estatus" wire:loading.attr="disabled" />
                            @endif


                        </x-list.button-group>

                    </td>

                </tr>

            @empty

                <tr>
                    <td colspan="7" class="text-center py-5">
                        No se encontraron registros.
                    </td>

                </tr>
            @endforelse

        </tbody>

    </x-list.table>

    {{ $transportes->links() }}

    <x-layout.loader.fullpage wire:loading.delay.short />

</div>

@script
    <script>
        Alpine.data('listTransporte', () => ({
            destroy() {
                this.toastCleanup?.forEach(cleanup => cleanup());
            },
            init() {
                this.toastCleanup = [
                    Livewire.on('successEventList', data => this.$store.toast.success(data.message)),
                    Livewire.on('errorEventList', data => this.$store.toast.info(data.message)),
                ];
                const savedMessage = @js(session()->pull('empresa_success'));
                if (savedMessage) this.$nextTick(() => Livewire.dispatch('successEventList', {
                    message: savedMessage
                }));
            },

        }));
    </script>
@endscript
