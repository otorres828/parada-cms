{{--
    PROGRAMACIONES — LISTADO
    --------------------------------------------------------------------------
    Permite consultar las salidas programadas con búsqueda, filtros por empresa, estado y fechas,
    ordenación y paginación. Muestra la ruta principal, la disponibilidad registrada y el precio
    de la primera tarifa asociada, con acceso a pasajeros según permisos.

    Componentes reutilizables utilizados:
    - <x-list.heading />: Cabecera del módulo con título y acciones.
    - <x-list.actions />: Contenedor del buscador y las acciones del listado.
    - <x-list.search-input />: Buscador vinculado al estado del listado.
    - <x-list.table />: Contenedor reutilizable de la tabla del listado.
    - <x-list.sortable-button />: Control para ordenar por una columna.
    - <x-list.status-badge />: Etiqueta visual del estado del registro.
    - <x-list.button-group />: Agrupación de los botones de acción de una fila.
    - <x-layout.loader.fullpage />: Indicador de carga durante las operaciones de Livewire.
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

            <label class="form-label" for="filtro-empresa">
                Empresa
            </label>

            <select id="filtro-empresa" class="form-select" wire:model.live="empresa_id">

                <option value="">Todas las empresas</option>

                @foreach ($empresas as $empresa)
                    <option value="{{ $empresa->id }}">{{ $empresa->nombre }}</option>
                @endforeach

            </select>

        </div>

        <div class="col-md-3">

            <label class="form-label" for="listProgramacion-status">
                Estado
            </label>

            <select id="listProgramacion-status" class="form-select" wire:model.live="status">

                <option value="">Todos</option>
                <option value="1">Activo</option>
                <option value="0">Inactivo</option>

            </select>

        </div>

        <div class="col-md-3">

            <label class="form-label" for="listProgramacion-from">
                Desde
            </label>
            <input id="listProgramacion-from" type="date" class="form-control" wire:model.live="date_from">
        </div>

        <div class="col-md-3">

            <label class="form-label" for="listProgramacion-to">
                Hasta
            </label>
            <input id="listProgramacion-to" type="date" class="form-control" wire:model.live="date_to">
        </div>

    </div>

    <x-list.table>

        <thead>

            <tr>
                <th>ID
                    <x-list.sortable-button column="id" :$sortColumn :$sortDirection />
                </th>

                <th>Empresa </th>

                <th>Origen </th>

                <th>Destino </th>

                <th>Fecha
                    <x-list.sortable-button column="fecha_salida" :$sortColumn :$sortDirection />
                </th>

                <th>Hora
                    <x-list.sortable-button column="hora_salida" :$sortColumn :$sortDirection />
                </th>

                <th>Disponibles
                    <x-list.sortable-button column="asientos_disponibles" :$sortColumn :$sortDirection />
                </th>

                <th>Precio USD</th>

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
                        {{ $programacion->viaje?->empresa?->nombre ?? '—' }}
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
                        {{ $programacion->asientos_disponibles ?? '—' }}
                    </td>

                    <td>
                        {{ number_format($programacion->tramoPrecios->first()?->precio ?? 0, 2) }}
                    </td>

                    <td>
                        <x-list.status-badge :status="$programacion->estatus" />
                    </td>

                    <td class="text-end">

                        <x-list.button-group>

                            @if ($canViewPassengers)
                                <a class="btn btn-outline-secondary"
                                    href="{{ route('admin.programaciones.passengers', ['programacion_id' => $programacion->id]) }}"
                                    wire:navigate title="Pasajeros" aria-label="Pasajeros"><i
                                        class="bi bi-people-fill"></i></a>
                            @endif

                        </x-list.button-group>

                    </td>

                </tr>

            @empty

                <tr>
                    <td colspan="10" class="text-center py-5">
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
                const savedMessage = @js(session()->pull('admin_success'));
                if (savedMessage) this.$nextTick(() => Livewire.dispatch('successEventList', {
                    message: savedMessage
                }));
            },

        }));
    </script>
@endscript
