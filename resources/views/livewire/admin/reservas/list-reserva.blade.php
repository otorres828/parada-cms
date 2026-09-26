{{--
    RESERVAS Y VENTAS — LISTADO
    --------------------------------------------------------------------------
    Permite consultar las compras por cliente, empresa y estado de pago, con búsqueda, filtros por
    empresa y fechas, ordenación y paginación. Muestra el origen y destino propios de cada
    reserva, su importe y el acceso al detalle según los permisos.

    Componentes reutilizables utilizados:
    - <x-layout.loader.fullpage />: Indicador global durante operaciones de Livewire.
    - <x-list.actions />: Contenedor del buscador y filtros del listado.
    - <x-list.button-group />: Agrupa las acciones disponibles por registro.
    - <x-list.heading />: Cabecera del módulo con título y acciones.
    - <x-list.search-input />: Buscador reactivo del listado.
    - <x-list.sortable-button />: Control de ordenación por columna.
    - <x-list.status-reserva />: Etiqueta del estado de pago de la reserva.
    - <x-list.table />: Contenedor reutilizable para tablas.
    - Botón Descargar Excel: Exporta las reservas que coinciden con los filtros aplicados.
    --------------------------------------------------------------------------
--}}

@section('title', 'Reservas y ventas')

<div x-data="listReserva" class="py-3">

    <x-list.heading>

        <x-slot:title>
            Reservas y ventas
        </x-slot:title>

    </x-list.heading>

    <x-list.actions>

        <x-slot:search>

            <x-list.search-input wire:model.live.debounce.1200ms="search" />

        </x-slot:search>

        <x-slot:group>

        </x-slot:group>

    </x-list.actions>

    <div class="row g-3 mb-3 align-items-end">

        <div class="col-md-6 col-xl-2">

            <label class="form-label" for="reserva-empresa">
                Empresa
            </label>

            <select id="reserva-empresa" class="form-select" wire:model.live="empresa_id">

                <option value="">Todas las empresas</option>

                @foreach ($empresas as $empresa)
                    <option value="{{ $empresa->id }}">{{ $empresa->nombre }}</option>
                @endforeach

            </select>

        </div>

        <div class="col-md-6 col-xl-2">

            <label class="form-label" for="listReserva-status">
                Estado
            </label>

            <select id="listReserva-status" class="form-select" wire:model.live="status">

                <option value="">Todos</option>
                <option value="{{ \App\Models\Reserva::ESTADO_PAGO_PAGADO }}">Pagada y Reembolsado</option>
                <option value="{{ \App\Models\Reserva::ESTADO_PAGO_PENDIENTE }}">Pendiente</option>
                <option value="{{ \App\Models\Reserva::ESTADO_PAGO_CANCELADO }}">Cancelada</option>
                <option value="{{ \App\Models\Reserva::ESTADO_PAGO_REPROGRAMADO }}">Reprogramada</option>
                <option value="{{ \App\Models\Reserva::ESTADO_PAGO_REEMBOLSADO }}">Reembolsada</option>

            </select>

        </div>

        <div class="col-md-6 col-xl-2">

            <label class="form-label" for="listReserva-from">
                Desde
            </label>
            <input id="listReserva-from" type="date" class="form-control" wire:model.live="date_from">
        </div>

        <div class="col-md-6 col-xl-2">

            <label class="form-label" for="listReserva-to">
                Hasta
            </label>
            <input id="listReserva-to" type="date" class="form-control" wire:model.live="date_to">
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
                <th>ID
                    <x-list.sortable-button column="id" :$sortColumn :$sortDirection />
                </th>

                <th>Referencia
                    <x-list.sortable-button column="codigo_referencia" :$sortColumn :$sortDirection />
                </th>

                <th>Cliente </th>

                <th>Empresa </th>

                <th>Origen</th>

                <th>Destino final</th>

                <th>Fecha
                    <x-list.sortable-button column="fecha_compra" :$sortColumn :$sortDirection />
                </th>

                <th>Total
                    <x-list.sortable-button column="monto_total" :$sortColumn :$sortDirection />
                </th>

                <th>Estado
                    <x-list.sortable-button column="estado_pago" :$sortColumn :$sortDirection />
                </th>

                <th></th>

            </tr>
        </thead>

        <tbody>

            @forelse ($reservas as $reserva)
                <tr wire:key="listReserva-{{ $reserva->id }}">
                    <td>
                        {{ $reserva->id }}
                    </td>

                    <td>
                        {{ $reserva->codigo_referencia ?? '—' }}
                    </td>

                    <td>
                        {{ $reserva->usuario?->name ?? '—' }}
                    </td>

                    <td>
                        {{ $reserva->programacion?->viaje?->empresa?->nombre ?? '—' }}
                    </td>

                    <td>
                        {{ $reserva->origenTerminal?->nombre ?? 'No registrado' }}
                    </td>

                    <td>
                        {{ $reserva->destinoTerminal?->nombre ?? 'No registrado' }}
                    </td>

                    <td>
                        {{ $reserva->fecha_compra?->format('d/m/Y H:i') ?? '—' }}
                    </td>

                    <td>
                        <x-money.dual :usd="$reserva->monto_total" :bs="$reserva->calcularMontoBs($reserva->monto_total)" />
                    </td>

                    <td>
                        <x-list.status-reserva :status="$reserva->estado_pago" />
                    </td>

                    <td class="text-end">

                        <x-list.button-group>

                            @if ($canDetail)
                                <a class="btn btn-outline-secondary"
                                    href="{{ route('admin.reservas.detail', ['reserva_id' => $reserva->id]) }}"
                                    wire:navigate title="Detalle" aria-label="Detalle"><i
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

    {{ $reservas->links() }}

    <x-layout.loader.fullpage wire:loading.delay.short />

</div>

@script
    <script>
        Alpine.data('listReserva', () => ({
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

