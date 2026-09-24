{{--
    ADMINISTRADORES — LISTADO
    --------------------------------------------------------------------------
    Permite consultar las cuentas administrativas. Incluye búsqueda, ordenación y paginación.
    Ofrece los filtros disponibles en la pantalla. Presenta las acciones de cada registro según
    las autorizaciones del administrador.

    Componentes reutilizables utilizados:
    - <x-layout.loader.fullpage />: Indicador global durante operaciones de Livewire.
    - <x-list.actions />: Contenedor del buscador y filtros del listado.
    - <x-list.add-button />: Botón para registrar un nuevo elemento.
    - <x-list.button-group />: Agrupa las acciones disponibles por registro.
    - <x-list.edit-button />: Enlace para editar el registro.
    - <x-list.heading />: Cabecera del módulo con título y acciones.
    - <x-list.search-input />: Buscador reactivo del listado.
    - <x-list.sortable-button />: Control de ordenación por columna.
    - <x-list.status-badge />: Etiqueta visual del estado del registro.
    - <x-list.table />: Contenedor reutilizable para tablas.
    --------------------------------------------------------------------------
--}}

@section('title', 'Administradores')

<div x-data="listAdmin" class="py-3">

    <x-list.heading>

        <x-slot:title>
            Administradores
        </x-slot:title>

    </x-list.heading>

    <x-list.actions>

        <x-slot:search>

            <x-list.search-input wire:model.live.debounce.1200ms="search" />

        </x-slot:search>

        <x-slot:group>

            <select id="listAdmin-status" class="form-select" style="width: 240px;" wire:model.live="status"
                aria-label="Filtrar por estado">
                <option value="">Todos los estados</option>
                <option value="1">Activo</option>
                <option value="2">Inactivo</option>
            </select>

        </x-slot:group>

        @if ($canAdd)
            <x-slot:button>
                <x-list.add-button :route="route('admin.admins.add')">
                    Nuevo registro
                </x-list.add-button>
            </x-slot:button>
        @endif

    </x-list.actions>

    <x-list.table>

        <thead>

            <tr>
                <th>ID
                    <x-list.sortable-button column="id" :$sortColumn :$sortDirection />
                </th>

                <th>Nombre
                    <x-list.sortable-button column="name" :$sortColumn :$sortDirection />
                </th>

                <th>Usuario
                    <x-list.sortable-button column="username" :$sortColumn :$sortDirection />
                </th>

                <th>Correo
                    <x-list.sortable-button column="email" :$sortColumn :$sortDirection />
                </th>

                <th>Estado
                    <x-list.sortable-button column="status" :$sortColumn :$sortDirection />
                </th>

                <th></th>

            </tr>
        </thead>

        <tbody>

            @forelse ($admins as $admin)
                <tr wire:key="listAdmin-{{ $admin->id }}">

                    <td>
                        {{ $admin->id }}
                    </td>

                    <td>
                        {{ $admin->name ?? '—' }}
                    </td>

                    <td>
                        {{ $admin->username ?? '—' }}
                    </td>

                    <td>
                        {{ $admin->email ?? '—' }}
                    </td>

                    <td>
                        <x-list.status-badge :status="$admin->status" />
                    </td>

                    <td class="text-end">

                        <x-list.button-group>

                            @if ($canEdit)
                                <x-list.edit-button :route="route('admin.admins.edit', ['admin_id' => $admin->id])" />
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

    {{ $admins->links() }}

    <x-layout.loader.fullpage wire:loading.delay.short />

</div>

@script
    <script>
        Alpine.data('listAdmin', () => ({
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

