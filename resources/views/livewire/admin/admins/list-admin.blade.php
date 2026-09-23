{{--
    ADMINISTRADORES — LISTADO
    --------------------------------------------------------------------------
    Permite consultar las cuentas administrativas. Incluye búsqueda, ordenación y paginación.
    Ofrece los filtros disponibles en la pantalla. Presenta las acciones de cada registro según
    las autorizaciones del administrador.

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
    - <x-layout.loader.fullpage />: Indicador de carga durante las operaciones de Livewire.
    --------------------------------------------------------------------------
--}}

@section('title', 'Administradores')

<div x-data="listAdmin" class="py-3">

    <x-list.heading>

        <x-slot:title>
            Administradores
        </x-slot:title>

        <x-slot:button>

            <x-list.add-button :route="route('admin.admins.add')">
                Nuevo registro
            </x-list.add-button>

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

            <label class="form-label" for="listAdmin-status">
                Estado
            </label>

            <select id="listAdmin-status" class="form-select" wire:model.live="status">

                <option value="">Todos</option>
                <option value="1">Activo</option>
                <option value="2">Inactivo</option>

            </select>

        </div>

    </div>

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
