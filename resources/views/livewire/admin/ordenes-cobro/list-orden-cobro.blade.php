{{--
    ÓRDENES DE COBRO — LISTADO
    --------------------------------------------------------------------------
    Consulta las órdenes emitidas a las empresas por las tasas de servicio cobradas.

    Componentes reutilizables utilizados:
    - <x-list.heading />: Cabecera del módulo.
    - <x-list.actions />: Buscador y filtros del listado.
    - <x-list.search-input />: Buscador reactivo.
    - <x-list.table />: Tabla paginada.
    - <x-list.view-button />: Acceso al detalle.
    - <x-layout.loader.fullpage />: Indicador global de carga.
    - Botón Descargar Excel: Exporta las órdenes que coinciden con los filtros aplicados.
    --------------------------------------------------------------------------
--}}

@section('title', 'Órdenes de cobro')

<div class="py-3">

    <x-list.heading>

        <x-slot:title>
            Órdenes de cobro
        </x-slot:title>

    </x-list.heading>

    <x-list.actions>

        <x-slot:search>
            <x-list.search-input wire:model.live.debounce.1200ms="search" />
        </x-slot:search>

    </x-list.actions>

    <div class="row g-3 mb-3 align-items-end">

        <div class="col-md-6 col-xl-2">
            <label class="form-label" for="orden-empresa">Empresa</label>
            <select id="orden-empresa" class="form-select" wire:model.live="empresa_id">
                <option value="">Todas</option>
                @foreach ($empresas as $empresa)
                    <option value="{{ $empresa->id }}">{{ $empresa->nombre }}</option>
                @endforeach
            </select>
        </div>

        <div class="col-md-6 col-xl-2">
            <label class="form-label" for="orden-estatus">Estado</label>
            <select id="orden-estatus" class="form-select" wire:model.live="estatus">
                <option value="">Todos</option>
                <option value="1">Emitidas</option>
                <option value="2">Pendientes</option>
                <option value="3">Rechazadas</option>
                <option value="4">Aprobadas</option>
            </select>
        </div>

        <div class="col-md-6 col-xl-2">
            <label class="form-label" for="orden-desde">Desde</label>
            <input id="orden-desde" class="form-control" type="date" wire:model.live="date_from">
        </div>

        <div class="col-md-6 col-xl-2">
            <label class="form-label" for="orden-hasta">Hasta</label>
            <input id="orden-hasta" class="form-control" type="date" wire:model.live="date_to">
        </div>

        @if ($canDownload)
            <div class="col-md-12 col-xl-auto ms-xl-auto text-md-end">
                <button type="button" class="btn btn-success" wire:click="exportExcel"
                    wire:loading.attr="disabled" wire:target="exportExcel">
                    <i class="bi bi-file-earmark-excel" aria-hidden="true"></i> Descargar Excel
                </button>
            </div>
        @endif

    </div>

    <x-list.table>

        <thead>
            <tr>
                <th>Código</th>
                <th>Empresa</th>
                <th>Período</th>
                <th>Reservas</th>
                <th>Total</th>
                <th>Vencimiento</th>
                <th>Estado</th>
                <th></th>
            </tr>
        </thead>

        <tbody>
            @forelse ($ordenes as $orden)
                <tr class="align-middle" wire:key="orden-cobro-{{ $orden->id }}">
                    <td>{{ $orden->codigo }}</td>
                    <td>{{ $orden->empresa->nombre }}</td>
                    <td>{{ $orden->periodo_desde->format('d/m/Y') }} — {{ $orden->periodo_hasta->format('d/m/Y') }}</td>
                    <td>{{ $orden->cantidad_reservas }}</td>
                    <td><x-money.dual :usd="$orden->total" :bs="data_get($conversionesBs, $orden->id . '.total_bs')" /></td>
                    <td>{{ $orden->fecha_vencimiento->format('d/m/Y H:i') }}</td>
                    <td>
                        <span @class([
                            'badge',
                            'text-bg-primary' => $orden->estatus === 1,
                            'text-bg-warning' => $orden->estatus === 2,
                            'text-bg-danger' => $orden->estatus === 3,
                            'text-bg-success' => $orden->estatus === 4,
                        ])>{{ $orden->getEstatusNombre() }}</span>
                    </td>
                    <td class="text-end">
                        @if ($canDetail)
                            <x-list.view-button :route="route('admin.ordenes-cobro.detail', $orden->id)" :target="false" />
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center py-5">No se encontraron órdenes de cobro.</td>
                </tr>
            @endforelse
        </tbody>

    </x-list.table>

    {{ $ordenes->links() }}

    <x-layout.loader.fullpage wire:loading.delay.short />

</div>
