{{--
    AUTOBUSES — LISTADO
    --------------------------------------------------------------------------
    Permite consultar los autobuses de las empresas. Incluye búsqueda, ordenación y paginación.
    Ofrece los filtros disponibles en la pantalla. Presenta las acciones de cada registro según
    las autorizaciones del administrador.

    Componentes reutilizables utilizados:
    - <x-list.heading />: Cabecera del módulo con título y acciones.
    - <x-list.actions />: Contenedor del buscador y las acciones del listado.
    - <x-list.search-input />: Buscador vinculado al estado del listado.
    - <x-list.table />: Contenedor reutilizable para la tabla del listado.
    - <x-list.sortable-button />: Control para ordenar por una columna.
    - <x-list.status-badge />: Etiqueta visual del estado del registro.
    - <x-list.button-group />: Agrupación de botones de acción de una fila.
    - <x-list.view-button />: Enlace para consultar el detalle del registro.
    - <x-layout.loader.fullpage />: Indicador de carga durante las operaciones de Livewire.
    --------------------------------------------------------------------------
--}}

@section('title', 'Autobuses')

<div x-data="listAutobus" class="py-3">

    <x-list.heading>

        <x-slot:title>
            Autobuses
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

            <label class="form-label" for="listAutobus-status">
                Estado
            </label>

            <select id="listAutobus-status" class="form-select" wire:model.live="status">

                <option value="">Todos</option>
                <option value="1">Activo</option>
                <option value="0">Inactivo</option>

            </select>

        </div>

    </div>

    <x-list.table>

        <thead>

            <tr>

                <th>ID
                    <x-list.sortable-button column="id" :$sortColumn :$sortDirection />
                </th>

                <th>Empresa </th>

                <th>Placa
                    <x-list.sortable-button column="placa" :$sortColumn :$sortDirection />
                </th>

                <th>Modelo
                    <x-list.sortable-button column="modelo" :$sortColumn :$sortDirection />
                </th>

                <th>Asientos
                    <x-list.sortable-button column="total_asientos" :$sortColumn :$sortDirection />
                </th>

                <th>Estado
                    <x-list.sortable-button column="estatus" :$sortColumn :$sortDirection />
                </th>

                <th></th>

            </tr>

        </thead>

        <tbody>

            @forelse ($autobuses as $autobus)

                <tr wire:key="listAutobus-{{ $autobus->id }}">

                    <td>
                        {{ $autobus->id }}
                    </td>

                    <td>
                        {{ $autobus->empresa?->nombre ?? '—' }}
                    </td>

                    <td>
                        {{ $autobus->placa ?? '—' }}
                    </td>

                    <td>
                        {{ $autobus->modelo ?? '—' }}
                    </td>

                    <td>
                        {{ $autobus->total_asientos ?? '—' }}
                    </td>

                    <td>
                        <x-list.status-badge :status="$autobus->estatus" />
                    </td>

                    <td class="text-end">

                        <x-list.button-group>

                            @if ($canDetail)

                                <x-list.view-button :route="route('admin.autobuses.detail', ['autobus_id' => $autobus->id])" :target="false" />

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

    {{ $autobuses->links() }}

    <x-layout.loader.fullpage wire:loading.delay.short />

</div>

@script
    <script>
        Alpine.data('listAutobus', () => ({
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
