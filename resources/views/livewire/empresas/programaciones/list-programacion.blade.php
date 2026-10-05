{{--
    PROGRAMACIONES — LISTADO
    --------------------------------------------------------------------------
    Permite consultar las salidas programadas con búsqueda, filtros por estado y fechas,
    ordenación y paginación. Muestra la ruta principal, fecha, hora y estado, con acceso a pasajeros según permisos.

    Componentes reutilizables utilizados:
    - <x-list.heading />: Elemento de presentación del listado.
    - <x-slot />: Elemento de presentación del listado.
    - <x-list.actions />: Elemento de presentación del listado.
    - <x-list.search-input />: Elemento de presentación del listado.
    - <x-list.table />: Tabla propia del panel.
    - <x-list.sortable-button />: Ordenación de columnas.
    - <x-list.status-programacion />: Elemento de presentación del listado.
    - <x-list.edit-button />: Edición de registros permitidos.
    - <x-list.status-button />: Activación e inactivación con permiso de edición.
    - <x-list.button-group />: Elemento de presentación del listado.
    - <x-layout.loader.fullpage />: Elemento de presentación del listado.
    --------------------------------------------------------------------------
--}}

@section('title', 'Programaciones')

<div x-data="listProgramacion" class="py-3">

    <x-list.heading>

        <x-slot:title>
            Programaciones
        </x-slot:title>

    </x-list.heading>

    <x-list.actions>

        <x-slot:search>

            <x-list.search-input wire:model.live.debounce.1200ms="search" />

        </x-slot:search>

        <x-slot:group>

            <select id="listProgramacion-status" class="form-select" style="width: 180px; max-width: 100%;"
                wire:model.live="status" aria-label="Filtrar por estado">

                <option value="">Todos los estados</option>
                <option value="1">Activo</option>
                <option value="2">Inactivo</option>
                <option value="3">Finalizados</option>

            </select>

            <div class="d-flex align-items-center gap-2">

                <input id="listProgramacion-from" type="date" class="form-control" style="width: 160px;"
                    wire:model.live="date_from" aria-label="Fecha inicial"
                    min="{{ $this->getMinFilterDate() }}" max="{{ $this->getMaxFilterDate() }}">

                <span class="text-body-secondary" aria-hidden="true">a</span>

                <input id="listProgramacion-to" type="date" class="form-control" style="width: 160px;"
                    wire:model.live="date_to" aria-label="Fecha final"
                    min="{{ $this->getMinFilterDate() }}" max="{{ $this->getMaxFilterDate() }}">

            </div>

        </x-slot:group>

        @if ($canAdd)

            <x-slot:button>
                <a class="btn btn-primary" href="{{ route('empresas.programaciones.add') }}" wire:navigate>
                    <i class="bi bi-plus-lg me-1"></i> Nuevo registro
                </a>
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

                <th>Fecha
                    <x-list.sortable-button column="salida_fecha" :$sortColumn :$sortDirection />
                </th>

                <th>Hora
                    <x-list.sortable-button column="salida_hora" :$sortColumn :$sortDirection />
                </th>

                <th>Estado
                    <x-list.sortable-button column="estatus" :$sortColumn :$sortDirection />
                </th>

                <th></th>

            </tr>
        </thead>

        <tbody>

            @forelse ($programaciones as $programacion)
                <tr wire:key="listProgramacion-{{ $programacion->id }}">

                    <td>
                        {{ $programacion->id }}
                    </td>

                    <td>
                        {{ $programacion->viaje?->origenTerminal?->nombre ?? '—' }}
                    </td>

                    <td>
                        {{ $programacion->viaje?->destinoTerminal?->nombre ?? '—' }}
                    </td>

                    <td>
                        {{ $programacion->getSalida()?->format('d/m/Y') ?? '—' }}
                    </td>

                    <td>
                        {{ $programacion->getSalida()?->format('H:i') ?? '—' }}
                    </td>

                    <td>

                        <x-list.status-programacion :status="$programacion->estatus" />

                    </td>

                    <td class="text-end">

                        <x-list.button-group>

                            @if ($canViewPassengers)
                                <a class="btn btn-outline-secondary"
                                    href="{{ route('empresas.programaciones.detail', ['programacion_id' => $programacion->id]) }}"
                                    wire:navigate title="Pasajeros" aria-label="Pasajeros"><i
                                        class="bi bi-people-fill"></i></a>
                            @endif

                            @if ($canEdit && in_array($programacion->estatus, [1, 2]))

                                <x-list.edit-button :route="route('empresas.programaciones.edit', ['programacion_id' => $programacion->id])" :disabled="$programacion->reservas_exists" disabled-reason="Esta programación tiene reservas y no puede editarse." />

                                <x-list.status-button wire:click="changeStatus({{ $programacion->id }})" :status="$programacion->estatus" wire:loading.attr="disabled" />

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

    {{ $programaciones->links() }}

    <x-layout.loader.fullpage wire:loading.delay.short />

</div>

@script
    <script>
        Alpine.data('listProgramacion', () => ({
            destroy() {
                this.toastCleanup?.forEach(cleanup => cleanup());
            },
            init() {
                const savedMessage = @js(session()->pull('empresas_programacion_success'));
                if (savedMessage) this.$nextTick(() => this.$store.toast.success(savedMessage));
                this.toastCleanup = [
                    Livewire.on('empresas_programacion_success', data => this.$store.toast.success(data.message)),
                    Livewire.on('empresas_programacion_error', data => this.$store.toast.info(data.message)),
                ];
            },

        }));
    </script>
@endscript
