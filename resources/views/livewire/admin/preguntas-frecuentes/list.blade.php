{{--
    PREGUNTAS FRECUENTES — LISTADO
    --------------------------------------------------------------------------
    Administra las preguntas y respuestas publicadas en el sitio. Permite buscar, filtrar,
    ordenar, cambiar el estado y eliminar registros según los permisos del administrador.

    Componentes reutilizables utilizados:
    - <x-list.heading />: Cabecera del módulo.
    - <x-list.actions />: Contenedor del buscador, filtro y acción de registro.
    - <x-list.search-input />: Buscador reactivo.
    - <x-list.table />: Contenedor reutilizable para la tabla.
    - <x-list.sortable-button />: Control de ordenación.
    - <x-list.status-badge />: Estado visual del registro.
    - <x-list.button-group />: Agrupación de acciones.
    - <x-list.status-button />: Activa o desactiva la pregunta.
    - <x-list.edit-button />: Enlace de edición.
    - <x-list.delete-button />: Elimina el registro.
    - <x-layout.loader.fullpage />: Indicador global de carga.
    --------------------------------------------------------------------------
--}}

@section('title', 'Preguntas frecuentes')

<div x-data="listPregunta" class="py-3">

    <x-list.heading>

        <x-slot:title>
            Preguntas frecuentes
        </x-slot:title>

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
                <x-list.add-button :route="route('admin.preguntas-frecuentes.add')">
                    Nueva pregunta
                </x-list.add-button>
            </x-slot:button>
        @endif

    </x-list.actions>

    <x-list.table>

        <thead>
            <tr>
                <th>
                    Orden
                    <x-list.sortable-button column="orden" :$sortColumn :$sortDirection />
                </th>
                <th>
                    Pregunta
                    <x-list.sortable-button column="pregunta" :$sortColumn :$sortDirection />
                </th>
                <th>Estado</th>
                <th></th>
            </tr>
        </thead>

        <tbody>
            @forelse ($preguntas as $pregunta)

                <tr wire:key="pregunta-{{ $pregunta->id }}">

                    <td>{{ $pregunta->orden }}</td>

                    <td>{{ $pregunta->pregunta }}</td>

                    <td>
                        <x-list.status-badge :status="$pregunta->estatus" />
                    </td>

                    <td class="text-end">

                        <x-list.button-group>

                            @if ($canEdit)
                                <x-list.status-button wire:click="changeStatus({{ $pregunta->id }})"
                                    :status="$pregunta->estatus" />
                                <x-list.edit-button :route="route('admin.preguntas-frecuentes.edit', $pregunta->id)" />
                            @endif

                            @if ($canDelete)
                                <x-list.delete-button x-data
                                    @click="$dispatch('confirmDeletion', { id: {{ $pregunta->id }}, type: 'single' })" />
                            @endif

                        </x-list.button-group>

                    </td>

                </tr>
                
            @empty
                <tr>
                    <td colspan="4" class="text-center py-5">
                        No se encontraron preguntas frecuentes.
                    </td>
                </tr>
            @endforelse
        </tbody>

    </x-list.table>

    {{ $preguntas->links() }}

    <x-layout.loader.fullpage wire:loading.delay.short />

</div>

@script
    <script>
        Alpine.data('listPregunta', () => ({
            init() {
                this.toastCleanup = [
                    Livewire.on('successEventList', data => this.$store.toast.success(data.message)),
                    Livewire.on('errorEventList', data => this.$store.toast.info(data.message)),
                ];

                const savedMessage = @js(session()->pull('admin_success'));
                if (savedMessage) this.$nextTick(() => Livewire.dispatch('successEventList', {
                    message: savedMessage,
                }));

                this.confirmDeletion = event => {
                    Swal.fire({
                        title: '¿Estás seguro de eliminar esta pregunta?',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Sí, eliminar',
                        cancelButtonText: 'Cancelar',
                    }).then(result => {
                        if (result.isConfirmed) this.$wire.deletePregunta(event.detail.id);
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
