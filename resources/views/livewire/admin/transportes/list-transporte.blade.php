{{--
    TRANSPORTES — LISTADO
    --------------------------------------------------------------------------
    Permite consultar los transportes de las empresas. Incluye búsqueda, ordenación y paginación.
    Ofrece los filtros disponibles en la pantalla. Presenta las acciones de cada registro según
    las autorizaciones del administrador.

    Componentes reutilizables utilizados:
    - <x-layout.loader.fullpage />: Indicador global durante operaciones de Livewire.
    - <x-list.actions />: Contenedor del buscador y filtros del listado.
    - <x-list.button-group />: Agrupa las acciones disponibles por registro.
    - <x-list.heading />: Cabecera del módulo con título y acciones.
    - <x-list.search-input />: Buscador reactivo del listado.
    - <x-list.sortable-button />: Control de ordenación por columna.
    - <x-list.status-badge />: Etiqueta visual del estado del registro.
    - <x-list.table />: Contenedor reutilizable para tablas.
    - <x-list.view-button />: Enlace para consultar el detalle del registro.
    --------------------------------------------------------------------------
--}}

@section('title', 'Transportes')

<div x-data="listTransporte" class="py-3">

    <x-list.heading>

        <x-slot:title>
            Transportes
        </x-slot:title>

    </x-list.heading>

    <x-list.actions>

        <x-slot:search>

            <x-list.search-input wire:model.live.debounce.1200ms="search" />

        </x-slot:search>

        <x-slot:group>

            <select id="tipo-transporte" class="form-select" style="width: 200px; max-width: 100%;" wire:model.live="tipo_transporte" aria-label="Filtrar por tipo de transporte">
                <option value="">Todos los transportes</option>
                <option value="autobus">Autobús</option>
                <option value="carro">Carro</option>
            </select>

            <select id="filtro-empresa" class="form-select" style="width: 240px; max-width: 100%;" wire:model.live="empresa_id" aria-label="Filtrar por empresa">

                <option value="">Todas las empresas</option>

                @foreach ($empresas as $empresa)
                    <option value="{{ $empresa->id }}">{{ $empresa->nombre }}</option>
                @endforeach

            </select>

            <select id="listTransporte-status" class="form-select" style="width: 180px; max-width: 100%;" wire:model.live="status" aria-label="Filtrar por estado">

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

                <th>Empresa </th>

                <th>Tipo de transporte</th>
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

            @forelse ($transportes as $transporte)
                <tr wire:key="listTransporte-{{ $transporte->id }}">

                    <td>
                        {{ $transporte->id }}
                    </td>

                    <td>
                        {{ $transporte->empresa?->nombre ?? '—' }}
                    </td>

                    <td>{{ $transporte->getTipoTransporte() }}</td>
                    <td>
                        {{ $transporte->placa ?? '—' }}
                    </td>

                    <td>
                        {{ $transporte->modelo ?? '—' }}
                    </td>

                    <td>
                        {{ $transporte->total_asientos ?? '—' }}
                    </td>

                    <td>

                        <x-list.status-badge :status="$transporte->estatus" />

                    </td>

                    <td class="text-end">

                        <x-list.button-group>

                            @if ($canDetail)

                                <x-list.view-button :route="route('admin.transportes.detail', ['transporte_id' => $transporte->id])" :target="false" />

                            @endif

                        </x-list.button-group>

                    </td>

                </tr>

            @empty

                <tr>
                    <td colspan="8" class="text-center py-5">
                        No se encontraron registros.
                    </td>

                </tr>
            @endforelse

        </tbody>

    </x-list.table>

    {{ $transportes->links() }}

    <x-layout.loader.fullpage wire:loading.delay.short />

</div>

@script
    <script>
        Alpine.data('listTransporte', () => ({
            destroy() {
                this.toastCleanup?.forEach(cleanup => cleanup());
            },
            init() {
                const savedMessage = @js(session()->pull('admin_transporte_success'));
                if (savedMessage) this.$nextTick(() => this.$store.toast.success(savedMessage));
                this.toastCleanup = [
                    Livewire.on('admin_transporte_success', data => this.$store.toast.success(data.message)),
                    Livewire.on('admin_transporte_error', data => this.$store.toast.info(data.message)),
                ];
            },

        }));
    </script>
@endscript
