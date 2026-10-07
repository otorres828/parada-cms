{{--
    USUARIOS DE EMPRESA — LISTADO
    --------------------------------------------------------------------------
    Permite consultar los usuarios de la empresa autenticada. Incluye búsqueda, ordenación y paginación.
    Ofrece los filtros disponibles en la pantalla. Presenta las acciones de cada registro según
    el acceso exclusivo del administrador empresarial.

    Componentes reutilizables utilizados:
    - <x-layout.loader.fullpage />: Indicador global durante operaciones de Livewire.
    - <x-list.actions />: Contenedor del buscador y filtros del listado.
    - <x-list.add-button />: Botón para registrar un nuevo elemento.
    - <x-list.button-group />: Agrupa las acciones disponibles por registro.
    - <x-list.delete-button />: Eliminación lógica con confirmación.
    - <x-list.edit-button />: Enlace para editar el registro.
    - <x-list.heading />: Cabecera del módulo con título y acciones.
    - <x-list.search-input />: Buscador reactivo del listado.
    - <x-list.sortable-button />: Control de ordenación por columna.
    - <x-list.status-badge />: Etiqueta visual del estado del registro.
    - <x-layout.error />: Resumen de errores de validación.
    - <x-list.status-button />: Cambio de estado de usuarios.
    - <x-list.table />: Contenedor reutilizable para tablas.
    --------------------------------------------------------------------------
--}}

@section('title', 'Usuarios')

<div x-data="listUsuarioEmpresa" class="py-3">

    <x-list.heading>

        <x-slot:title>
            Usuarios
        </x-slot:title>

    </x-list.heading>

    <x-list.actions>

        <x-slot:search>

            <x-list.search-input wire:model.live.debounce.1200ms="search" />

        </x-slot:search>

        <x-slot:group>

            <select id="listUsuarioEmpresa-status" class="form-select" style="width: 240px;" wire:model.live="status"
                aria-label="Filtrar por estado">
                <option value="">Todos los estados</option>
                <option value="1">Activo</option>
                <option value="2">Inactivo</option>
            </select>

        </x-slot:group>

        <x-slot:button>

            <x-list.add-button :route="route('empresas.usuarios.add')" >
                Nuevo registro
            </x-list.add-button>

        </x-slot:button>

    </x-list.actions>

    <x-layout.error />

    <x-list.table>

        <thead>

            <tr>
                <th>ID
                    <x-list.sortable-button column="id" :$sortColumn :$sortDirection />
                </th>

                <th>Nombre
                    <x-list.sortable-button column="nombre" :$sortColumn :$sortDirection />
                </th>

                <th>Tipo de usuario</th>

                <th>Correo
                    <x-list.sortable-button column="email" :$sortColumn :$sortDirection />
                </th>

                <th>Estado
                    <x-list.sortable-button column="estatus" :$sortColumn :$sortDirection />
                </th>

                <th></th>

            </tr>
        </thead>

        <tbody>

            @forelse ($usuarios as $usuario)
                <tr wire:key="listUsuarioEmpresa-{{ $usuario->id }}">

                    <td>
                        {{ $usuario->id }}
                    </td>

                    <td>
                        {{ $usuario->nombre ?? '—' }}
                    </td>

                    <td>
                        {{ $usuario->isAdmin() ? 'Administrador' : 'Usuario' }}
                    </td>

                    <td>
                        {{ $usuario->email ?? '—' }}
                    </td>

                    <td>

                        <x-list.status-badge :status="$usuario->estatus" />

                    </td>

                    <td class="text-end">

                        <x-list.button-group>

                            <x-list.edit-button :route="route('empresas.usuarios.edit', ['usuario_empresa_id' => $usuario->id])" :target="false" />

                            @if (! $usuario->isAdmin() && $usuario->id !== $usuarioActualId)
                                <x-list.status-button wire:click="changeStatus({{ $usuario->id }})" :status="$usuario->estatus" wire:loading.attr="disabled" />
                                <x-list.delete-button x-data @click="$dispatch('confirmDeletion', { id: {{ $usuario->id }} })" wire:loading.attr="disabled" />
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

    {{ $usuarios->links() }}

    <x-layout.loader.fullpage wire:loading.delay.short />

</div>

@script
    <script>
        Alpine.data('listUsuarioEmpresa', () => ({
            destroy() {
                this.toastCleanup?.forEach(cleanup => cleanup());
                window.removeEventListener('confirmDeletion', this.confirmDeletion);
            },
            init() {
                const savedMessage = @js(session()->pull('empresas_usuario_success'));
                if (savedMessage) this.$nextTick(() => this.$store.toast.success(savedMessage));
                this.toastCleanup = [
                    Livewire.on('empresas_usuario_success', data => this.$store.toast.success(data.message)),
                    Livewire.on('empresas_usuario_error', data => this.$store.toast.info(data.message)),
                ];

                this.confirmDeletion = event => {
                    Swal.fire({
                        title: '¿Estás seguro de eliminar este usuario?',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Sí, eliminar',
                        cancelButtonText: 'Cancelar',
                    }).then(result => {
                        if (result.isConfirmed) this.$wire.deleteUsuario(event.detail.id);
                    });
                };

                window.addEventListener('confirmDeletion', this.confirmDeletion);
            },

        }));
    </script>
@endscript
