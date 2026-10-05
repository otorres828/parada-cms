{{--
    CATEGORÍAS DEL CENTRO DE AYUDA — LISTADO
    --------------------------------------------------------------------------
    Administra las categorías que agrupan las preguntas frecuentes del sitio público.
    Permite buscar, filtrar, ordenar, cambiar el estado y eliminar categorías vacías.

    Componentes reutilizables utilizados:
    - <x-list.heading />: Cabecera y regreso al listado de preguntas.
    - <x-list.actions />: Buscador, filtro y acción de registro.
    - <x-list.table />: Tabla del listado.
    - <x-list.status-badge />: Estado visual del registro.
    - <x-list.button-group />: Agrupación de acciones.
    - <x-list.status-button />: Activa o desactiva la categoría.
    - <x-list.edit-button />: Enlace de edición.
    - <x-list.delete-button />: Elimina lógicamente una categoría vacía.
    - <x-layout.loader.fullpage />: Indicador global de carga.
    --------------------------------------------------------------------------
--}}

@section('title', 'Categorías del centro de ayuda')

<div x-data="listCategoriaPregunta" class="py-3">

    <x-list.heading>

        <x-slot:title>
            Categorías del centro de ayuda
        </x-slot:title>

        <x-slot:button>

            <x-form.cancel-button :link="route('admin.preguntas-frecuentes.list')" >
                Volver a preguntas
            </x-form.cancel-button>

        </x-slot:button>

    </x-list.heading>

    <x-list.actions>

        <x-slot:search>

            <x-list.search-input wire:model.live.debounce.1200ms="search" />

        </x-slot:search>

        <x-slot:group>
            <select class="form-select" style="width: 240px;" wire:model.live="estatus"
                aria-label="Filtrar por estado">
                <option value="">Todos los estados</option>
                <option value="1">Activas</option>
                <option value="2">Inactivas</option>
            </select>
        </x-slot:group>

        @if ($canAdd)

            <x-slot:button>

                <x-list.add-button :route="route('admin.preguntas-frecuentes.categorias.add')" >
                    Nueva categoría
                </x-list.add-button>

            </x-slot:button>

        @endif

    </x-list.actions>

    <x-list.table>

        <thead>
            <tr>
                <th>
                    Orden
                </th>

                <th>
                    Categoría
                </th>

                <th>
                    Slug
                </th>

                <th>
                    Preguntas
                </th>

                <th>
                    Destacada
                </th>

                <th>
                    Estado
                </th>

                <th></th>
            </tr>
        </thead>

        <tbody>
            @forelse ($categorias as $categoria)

                <tr wire:key="categoria-pregunta-{{ $categoria->id }}">

                    <td>{{ $categoria->orden }}</td>

                    <td>
                        @if ($categoria->icono)
                            <i class="bi {{ $categoria->icono }} me-2" aria-hidden="true"></i>
                        @endif
                        {{ $categoria->nombre }}
                    </td>

                    <td>{{ $categoria->slug }}</td>

                    <td>{{ $categoria->preguntas_count }}</td>

                    <td>{{ $categoria->destacada ? 'Sí' : 'No' }}</td>

                    <td>

                        <x-list.status-badge :status="$categoria->estatus" />

                    </td>

                    <td class="text-end">

                        <x-list.button-group>

                            @if ($canEdit)

                                <x-list.status-button wire:click="changeStatus({{ $categoria->id }})" :status="$categoria->estatus" />

                                <x-list.edit-button :route="route('admin.preguntas-frecuentes.categorias.edit', $categoria->id)" />

                            @endif

                            @if ($canDelete)

                                <x-list.delete-button x-data @click="$dispatch('confirmDeletion', { id: {{ $categoria->id }} })" />

                            @endif

                        </x-list.button-group>

                    </td>

                </tr>

            @empty

                <tr>
                    <td colspan="7" class="text-center py-5">
                        No se encontraron categorías.
                    </td>
                </tr>

            @endforelse

        </tbody>

    </x-list.table>

    {{ $categorias->links() }}

    <x-layout.loader.fullpage wire:loading.delay.short />

</div>

@script
    <script>
        Alpine.data('listCategoriaPregunta', () => ({
            init() {
                const savedMessage = @js(session()->pull('admin_categoria_pregunta_success'));
                if (savedMessage) this.$nextTick(() => this.$store.toast.success(savedMessage));
                this.toastCleanup = [
                    Livewire.on('admin_categoria_pregunta_success', data => this.$store.toast.success(data.message)),
                    Livewire.on('admin_categoria_pregunta_error', data => this.$store.toast.info(data.message)),
                ];


                this.confirmDeletion = event => {
                    Swal.fire({
                        title: '¿Estás seguro de eliminar esta categoría?',
                        text: 'Solo puede eliminarse si no contiene preguntas.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Sí, eliminar',
                        cancelButtonText: 'Cancelar',
                    }).then(result => {
                        if (result.isConfirmed) this.$wire.deleteCategoria(event.detail.id);
                    });
                };

                window.addEventListener('confirmDeletion', this.confirmDeletion);
            },
            destroy() {
                this.toastCleanup?.forEach(cleanup => cleanup());
                window.removeEventListener('confirmDeletion', this.confirmDeletion);
            },
        }));
    </script>
@endscript
