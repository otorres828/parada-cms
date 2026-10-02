{{--
    PROGRAMACIONES — LISTADO
    --------------------------------------------------------------------------
    Permite consultar las salidas programadas con búsqueda, filtros por empresa, estado y fechas,
    ordenación y paginación. Muestra la ruta principal, fecha, hora y estado, con acceso a pasajeros según permisos.

    Componentes reutilizables utilizados:
    - <x-list.heading />: Elemento de presentación del listado.
    - <x-slot />: Elemento de presentación del listado.
    - <x-list.actions />: Elemento de presentación del listado.
    - <x-list.search-input />: Elemento de presentación del listado.
    - <x-list.table />: Tabla propia del panel.
    - <x-list.sortable-button />: Ordenación de columnas.
    - <x-list.status-programacion />: Elemento de presentación del listado.
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

        </x-slot:group>

    </x-list.actions>

    <div class="row g-3 mb-3">

        <div class="col-md-3">

            <label class="form-label" for="listProgramacion-status">
                Estado
            </label>

            <select id="listProgramacion-status" class="form-select" wire:model.live="status">

                <option value="">Todos</option>
                <option value="1">Activo</option>
                <option value="2">Inactivo</option>
                <option value="3">Finalizados</option>

            </select>

        </div>

        <div class="col-md-3">

            <label class="form-label" for="listProgramacion-from">
                Desde
            </label>
            <input id="listProgramacion-from" type="date" class="form-control" wire:model.live="date_from"
                min="{{ $this->getMinFilterDate() }}" max="{{ $this->getMaxFilterDate() }}">
        </div>

        <div class="col-md-3">

            <label class="form-label" for="listProgramacion-to">
                Hasta
            </label>
            <input id="listProgramacion-to" type="date" class="form-control" wire:model.live="date_to"
                min="{{ $this->getMinFilterDate() }}" max="{{ $this->getMaxFilterDate() }}">
        </div>

    </div>

    <x-list.table>

        <thead>

            <tr>
                <th>ID
                    <x-list.sortable-button column="id" :$sortColumn :$sortDirection />
                </th>


                <th>Origen </th>

                <th>Destino </th>

                <th>Fecha
                    <x-list.sortable-button column="fecha_salida" :$sortColumn :$sortDirection />
                </th>

                <th>Hora
                    <x-list.sortable-button column="hora_salida" :$sortColumn :$sortDirection />
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
                        {{ $programacion->fecha_salida?->format('d/m/Y') ?? '—' }}
                    </td>

                    <td>
                        {{ $programacion->hora_salida ?? '—' }}
                    </td>

                    <td>
                        <x-list.status-programacion :status="$programacion->estatus" />
                    </td>

                    <td class="text-end">

                        <x-list.button-group>


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
