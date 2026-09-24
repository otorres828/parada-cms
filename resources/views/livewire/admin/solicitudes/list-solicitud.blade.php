{{--
    SOLICITUDES DE EMPRESAS — LISTADO
    --------------------------------------------------------------------------
    Muestra las solicitudes enviadas por agencias interesadas desde el formulario público.
    Permite buscar por sus datos, filtrar por estado y abrir el detalle para su seguimiento.

    Componentes reutilizables utilizados:
    - <x-layout.loader.fullpage />: Indicador global durante operaciones de Livewire.
    - <x-list.actions />: Contenedor del buscador y los filtros.
    - <x-list.button-group />: Agrupa las acciones disponibles por registro.
    - <x-list.check-all />: Selecciona los registros visibles en la página.
    - <x-list.delete-all-button />: Elimina las solicitudes seleccionadas.
    - <x-list.delete-button />: Elimina una solicitud individual.
    - <x-list.heading />: Cabecera del módulo.
    - <x-list.search-input />: Buscador reactivo del listado.
    - <x-list.sortable-button />: Control de ordenación por columna.
    - <x-list.table />: Contenedor reutilizable para tablas.
    - <x-list.view-button />: Enlace al detalle de la solicitud.
    --------------------------------------------------------------------------
--}}

@section('title', 'Solicitudes')

<div x-data="listSolicitud" class="py-3">

    <x-list.heading>

        <x-slot:title>
            Solicitudes de empresas
        </x-slot:title>

    </x-list.heading>

    <x-list.actions>

        <x-slot:search>
            <x-list.search-input wire:model.live.debounce.1200ms="search" />
        </x-slot:search>

        <x-slot:group>

            <div class="d-inline-flex align-items-center gap-2">

                <select class="form-select" style="width: 240px;" wire:model.live="estatus"
                    aria-label="Filtrar por estado">
                    <option value="">Todos los estados</option>
                    <option value="1">Nuevas</option>
                    <option value="2">Contactadas</option>
                    <option value="3">Cerradas</option>
                </select>

                @if ($canDelete)
                    <x-list.delete-all-button x-show="$wire.selectedRecordIds.length > 0" x-data
                        @click="$dispatch('confirmDeletion', { type: 'batch' })" />
                @endif

            </div>

        </x-slot:group>

    </x-list.actions>

    <x-list.table>

        <thead>
            <tr>
                @if ($canDelete)
                    <th class="w-20px">
                        <x-list.check-all />
                    </th>
                @endif

                <th>
                    ID
                    <x-list.sortable-button column="id" :$sortColumn :$sortDirection />
                </th>
                <th>Empresa</th>
                <th>Representante</th>
                <th>Contacto</th>
                <th>Ciudad</th>
                <th>
                    Estado
                    <x-list.sortable-button column="estatus" :$sortColumn :$sortDirection />
                </th>
                <th>
                    Fecha
                    <x-list.sortable-button column="created_at" :$sortColumn :$sortDirection />
                </th>
                <th></th>
            </tr>
        </thead>

        <tbody>

            @forelse ($solicitudes as $solicitud)
                <tr class="align-middle" wire:key="solicitud-{{ $solicitud->id }}">
                    @if ($canDelete)
                        <td>
                            <input class="form-check-input" type="checkbox" wire:model="selectedRecordIds"
                                value="{{ $solicitud->id }}">
                        </td>
                    @endif

                    <td>{{ $solicitud->id }}</td>
                    <td>{{ $solicitud->empresa }}</td>
                    <td>
                        {{ $solicitud->nombre }}
                        <div class="small text-body-secondary">{{ $solicitud->cargo }}</div>
                    </td>
                    <td>
                        {{ $solicitud->telefono }}
                        <div class="small text-body-secondary">{{ $solicitud->email }}</div>
                    </td>
                    <td>{{ $solicitud->ciudad ?: '—' }}</td>
                    <td>
                        <span @class([
                            'badge',
                            'text-bg-primary' => $solicitud->estatus === 1,
                            'text-bg-warning' => $solicitud->estatus === 2,
                            'text-bg-secondary' => $solicitud->estatus === 3,
                        ])>
                            {{ $solicitud->estatus_nombre }}
                        </span>
                    </td>
                    <td>{{ $solicitud->created_at?->format('d/m/Y H:i') }}</td>
                    <td class="text-end">

                        <x-list.button-group>

                            @if ($canDetail)
                                <x-list.view-button :route="route('admin.solicitudes.detail', $solicitud->id)" :target="false" />
                            @endif

                            @if ($canDelete)
                                <x-list.delete-button x-data
                                    @click="$dispatch('confirmDeletion', { id: {{ $solicitud->id }}, type: 'single' })" />
                            @endif

                        </x-list.button-group>

                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="{{ $canDelete ? 9 : 8 }}" class="text-center py-5">
                        No se encontraron solicitudes.
                    </td>
                </tr>
            @endforelse

        </tbody>

    </x-list.table>

    {{ $solicitudes->links() }}

    <x-layout.loader.fullpage wire:loading.delay.short />

</div>

@script
    <script>
        Alpine.data('listSolicitud', () => ({
            destroy() {
                this.toastCleanup?.forEach(cleanup => cleanup());
                window.removeEventListener('confirmDeletion', this.confirmDeletion);
            },
            init() {
                this.toastCleanup = [
                    Livewire.on('successEventList', data => this.$store.toast.success(data.message)),
                    Livewire.on('errorEventList', data => this.$store.toast.info(data.message)),
                ];

                this.confirmDeletion = event => {
                    const batch = event.detail.type === 'batch';

                    Swal.fire({
                        title: batch
                            ? '¿Estás seguro de eliminar las solicitudes seleccionadas?'
                            : '¿Estás seguro de eliminar esta solicitud?',
                        text: 'Esta acción no se puede deshacer.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Sí, eliminar',
                        cancelButtonText: 'Cancelar',
                    }).then(result => {
                        if (! result.isConfirmed) return;

                        if (batch) {
                            this.$wire.deleteSolicitudes();
                        } else {
                            this.$wire.deleteSolicitud(event.detail.id);
                        }
                    });
                };

                window.addEventListener('confirmDeletion', this.confirmDeletion);
            },
        }));
    </script>
@endscript
