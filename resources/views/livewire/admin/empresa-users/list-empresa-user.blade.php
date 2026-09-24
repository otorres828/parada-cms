{{--
    USUARIOS DE EMPRESA — LISTADO
    --------------------------------------------------------------------------
    Permite consultar las cuentas del personal de la empresa. Incluye búsqueda, ordenación y
    paginación. Ofrece los filtros disponibles en la pantalla. Presenta las acciones de cada
    registro según las autorizaciones del administrador.

    Componentes reutilizables utilizados:
    - <x-layout.loader.fullpage />: Indicador global durante operaciones de Livewire.
    - <x-list.actions />: Contenedor del buscador y filtros del listado.
    - <x-list.button-group />: Agrupa las acciones disponibles por registro.
    - <x-list.edit-button />: Enlace para editar el registro.
    - <x-list.heading />: Cabecera del módulo con título y acciones.
    - <x-list.search-input />: Buscador reactivo del listado.
    - <x-list.sortable-button />: Control de ordenación por columna.
    - <x-list.status-badge />: Etiqueta visual del estado del registro.
    - <x-list.status-button />: Acción para cambiar el estado del registro.
    - <x-list.table />: Contenedor reutilizable para tablas.
    - <x-list.view-button />: Enlace para consultar el detalle del registro.
    --------------------------------------------------------------------------
--}}

@section('title', 'Usuarios de empresa')

<div x-data="listEmpresaUser" class="py-3">

    <x-list.heading>

        <x-slot:title>
            Usuarios de empresa
        </x-slot:title>

    </x-list.heading>

    <x-list.actions>

        <x-slot:search>

            <x-list.search-input wire:model.live.debounce.1200ms="search" />

        </x-slot:search>

        <x-slot:group>

            <select id="listEmpresaUser-status" class="form-select" style="width: 240px;" wire:model.live="status"
                aria-label="Filtrar por estado">
                <option value="">Todos los estados</option>
                <option value="1">Activo</option>
                <option value="0">Inactivo</option>
            </select>

        </x-slot:group>

    </x-list.actions>

    <x-list.table>

        <thead>

            <tr>
                <th>ID
                    <x-list.sortable-button column="id" :$sortColumn :$sortDirection />
                </th>

                <th>Nombre
                    <x-list.sortable-button column="nombre" :$sortColumn :$sortDirection />
                </th>

                <th>Correo
                    <x-list.sortable-button column="email" :$sortColumn :$sortDirection />
                </th>

                <th>Administrador
                    <x-list.sortable-button column="es_admin" :$sortColumn :$sortDirection />
                </th>

                <th>Estado
                    <x-list.sortable-button column="estatus" :$sortColumn :$sortDirection />
                </th>

                <th></th>

            </tr>
        </thead>

        <tbody>

            @forelse ($usuariosEmpresa as $usuarioEmpresa)
                <tr wire:key="listEmpresaUser-{{ $usuarioEmpresa->id }}">
                    <td>
                        {{ $usuarioEmpresa->id }}
                    </td>

                    <td>
                        {{ $usuarioEmpresa->nombre ?? '—' }}
                    </td>

                    <td>
                        {{ $usuarioEmpresa->email ?? '—' }}
                    </td>

                    <td>
                        {{ $usuarioEmpresa->es_admin ? 'Sí' : 'No' }}
                    </td>

                    <td>
                        <x-list.status-badge :status="$usuarioEmpresa->estatus" />
                    </td>

                    <td class="text-end">

                        <x-list.button-group>

                            @if ($canDetail)
                                <x-list.view-button :route="route('admin.empresas.users.detail', [
                                    'empresa_id' => $empresa_id,
                                    'usuario_empresa_id' => $usuarioEmpresa->id,
                                ])" :target="false" />
                            @endif

                            @if ($canEdit)
                                <x-list.edit-button :route="route('admin.empresas.users.edit', [
                                    'empresa_id' => $empresa_id,
                                    'usuario_empresa_id' => $usuarioEmpresa->id,
                                ])" />
                            @endif

                            @if ($canPermissions)
                                <a class="btn btn-outline-secondary"
                                    href="{{ route('admin.empresas.users.permissions', ['empresa_id' => $empresa_id, 'usuario_empresa_id' => $usuarioEmpresa->id]) }}"
                                    wire:navigate title="Permisos" aria-label="Permisos"><i
                                        class="bi bi-shield-lock-fill"></i></a>
                            @endif

                            @if ($canEdit)
                                <x-list.status-button wire:click="changeStatus({{ $usuarioEmpresa->id }})"
                                    :status="$usuarioEmpresa->estatus"
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

    {{ $usuariosEmpresa->links() }}

    <x-layout.loader.fullpage wire:loading.delay.short />

</div>

@script
    <script>
        Alpine.data('listEmpresaUser', () => ({
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
