{{--
    RUTAS DE VIAJES — LISTADO
    --------------------------------------------------------------------------
    Permite consultar las rutas de viaje. Incluye búsqueda, ordenación y paginación. Ofrece los
    filtros disponibles en la pantalla. Presenta las acciones de cada registro según las
    autorizaciones del usuario empresarial.

    Componentes reutilizables utilizados:
    - <x-list.heading />: Elemento de presentación del listado.
    - <x-slot />: Elemento de presentación del listado.
    - <x-list.actions />: Elemento de presentación del listado.
    - <x-list.search-input />: Elemento de presentación del listado.
    - <x-list.table />: Tabla propia del panel.
    - <x-list.sortable-button />: Ordenación de columnas.
    - <x-list.status-badge />: Elemento de presentación del listado.
    - <x-list.button-group />: Elemento de presentación del listado.
    - <x-layout.loader.fullpage />: Elemento de presentación del listado.
    --------------------------------------------------------------------------
--}}

@section('title', 'Rutas de viajes')

<div x-data="listViaje" class="py-3">

    <x-list.heading>

        <x-slot:title>
            Rutas de viajes
        </x-slot:title>

    </x-list.heading>

    <x-list.actions>

        <x-slot:search>

            <x-list.search-input wire:model.live.debounce.1200ms="search" />

        </x-slot:search>

        <x-slot:group>

            <select id="listViaje-status" class="form-select" style="width: 220px; max-width: 100%;" aria-label="Estado" wire:model.live="status">

                <option value="">Todos</option>
                <option value="1">Activo</option>
                <option value="2">Inactivo</option>

            </select>

        </x-slot:group>

        @if ($canAdd)
            <x-slot:button>
                <a class="btn btn-primary" href="{{ route('empresas.viajes.add') }}" wire:navigate><i class="bi bi-plus-lg me-1" aria-hidden="true"></i> Nuevo registro</a>
            </x-slot:button>
        @endif
    </x-list.actions>

    <x-list.table>

        <thead>

            <tr>
                <th>ID
                    <x-list.sortable-button column="id" :$sortColumn :$sortDirection />
                </th>


                <th>Origen </th>

                <th>Destino </th>

                <th>Duración
                    <x-list.sortable-button column="duracion_estimada" :$sortColumn :$sortDirection />
                </th>

                <th>Estado
                    <x-list.sortable-button column="estatus" :$sortColumn :$sortDirection />
                </th>

                <th></th>

            </tr>
        </thead>

        <tbody>

            @forelse ($viajes as $viaje)

                <tr wire:key="listViaje-{{ $viaje->id }}">

                    <td>
                        {{ $viaje->id }}
                    </td>


                    <td>
                        {{ $viaje->origenTerminal?->nombre ?? '—' }}
                    </td>

                    <td>
                        {{ $viaje->destinoTerminal?->nombre ?? '—' }}
                    </td>

                    <td>
                        {{ $viaje->duracion_estimada ?? '—' }}
                    </td>

                    <td>
                        <x-list.status-badge :status="$viaje->estatus" />
                    </td>

                    <td class="text-end">

                        <x-list.button-group>
                            @if ($canEdit)
                                <a class="btn btn-outline-secondary btn-sm" href="{{ route('empresas.viajes.edit', $viaje->id) }}" wire:navigate aria-label="Editar ruta"><i class="bi bi-pencil" aria-hidden="true"></i></a>
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

    {{ $viajes->links() }}

    <x-layout.loader.fullpage wire:loading.delay.short />

</div>

@script
    <script>
        Alpine.data('listViaje', () => ({
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
