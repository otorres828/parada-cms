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

        </x-slot:group>

    </x-list.actions>

    <div class="row g-3 mb-3">
            <div class="col-md-3">
                <label class="form-label" for="tipo-transporte">Tipo de transporte</label>
                <select id="tipo-transporte" class="form-select" wire:model.live="tipo_transporte">
                    <option value="">Todos los transportes</option>
                    <option value="autobus">Autobús</option>
                    <option value="carro">Carro</option>
                </select>
            </div>

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

            <label class="form-label" for="listTransporte-status">
                Estado
            </label>

            <select id="listTransporte-status" class="form-select" wire:model.live="status">

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

