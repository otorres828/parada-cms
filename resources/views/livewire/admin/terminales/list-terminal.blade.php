{{--
    TERMINALES — LISTADO
    --------------------------------------------------------------------------
    Permite consultar las terminales de origen y destino. Incluye búsqueda, ordenación y
    paginación. Ofrece los filtros disponibles en la pantalla. Presenta las acciones de cada
    registro según las autorizaciones del administrador.

    Componentes reutilizables utilizados:
    - <x-list.heading />: Cabecera del módulo con título y acciones.
    - <x-list.add-button />: Enlace para abrir el formulario de alta.
    - <x-list.actions />: Contenedor del buscador y las acciones del listado.
    - <x-list.search-input />: Buscador vinculado al estado del listado.
    - <x-list.table />: Contenedor reutilizable de la tabla del listado.
    - <x-list.sortable-button />: Control para ordenar por una columna.
    - <x-list.status-badge />: Etiqueta visual del estado del registro.
    - <x-list.button-group />: Agrupación de los botones de acción de una fila.
    - <x-list.edit-button />: Enlace para editar el registro.
    - <x-list.status-button />: Botón para solicitar un cambio de estado.
    - <x-layout.loader.fullpage />: Indicador de carga durante las operaciones de Livewire.
    --------------------------------------------------------------------------
--}}

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

                <th></th>

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
