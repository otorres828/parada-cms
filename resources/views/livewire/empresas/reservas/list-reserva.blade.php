{{--
    RESERVAS Y VENTAS — LISTADO
    --------------------------------------------------------------------------
    Permite consultar las compras por cliente y estado de pago, con búsqueda, filtros por
    fechas, ordenación y paginación. Muestra el origen y destino propios de cada
    reserva, su importe y el acceso al detalle según los permisos.

    Componentes reutilizables utilizados:
    - <x-list.heading />: Elemento de presentación del listado.
    - <x-slot />: Elemento de presentación del listado.
    - <x-list.actions />: Elemento de presentación del listado.
    - <x-list.search-input />: Elemento de presentación del listado.
    - <x-list.table />: Tabla propia del panel.
    - <x-list.sortable-button />: Ordenación de columnas.
    - <x-money.dual />: Montos y su equivalente en bolívares.
    - <x-list.status-reserva />: Elemento de presentación del listado.
    - <x-list.button-group />: Elemento de presentación del listado.
    - <x-layout.loader.fullpage />: Elemento de presentación del listado.
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
            <input id="listReserva-from" type="date" class="form-control" wire:model.live="date_from"
                min="{{ $this->getMinFilterDate() }}" max="{{ $this->getMaxFilterDate() }}">
        </div>

        <div class="col-md-6 col-xl-2">

            <label class="form-label" for="listReserva-to">
                Hasta
            </label>
            <input id="listReserva-to" type="date" class="form-control" wire:model.live="date_to"
                min="{{ $this->getMinFilterDate() }}" max="{{ $this->getMaxFilterDate() }}">
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

                <th>Transporte</th>

                <th>Pasajes</th>

                <th>Ruta</th>

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


                    <td>{{ $reserva->programacion?->transporte?->getTipoTransporte() ?? 'No registrado' }}</td>

                    <td>
                        {{ $reserva->pasajes->count() }}
                    </td>

                    <td>
                        {{ $reserva->origenTerminal?->nombre ?? 'No registrado' }} →  {{ $reserva->destinoTerminal?->nombre ?? 'No registrado' }}
                    </td>

                    <td>
                        {{ $reserva->fecha_compra?->format('d/m/Y H:i') ?? '—' }}
                    </td>

                    <td>
                        <x-money.dual :usd="$ellosReciben ? $reserva->monto_total : $reserva->getMontoSinTasa()" :bs="$reserva->calcularMontoBs($ellosReciben ? $reserva->monto_total : $reserva->getMontoSinTasa())" />
                    </td>

                    <td>
                        <x-list.status-reserva :status="$reserva->estado_pago" />
                    </td>

                    <td class="text-end">

                        <x-list.button-group>


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
                const savedMessage = @js(session()->pull('empresa_success'));
                if (savedMessage) this.$nextTick(() => Livewire.dispatch('successEventList', {
                    message: savedMessage
                }));
            },

        }));
    </script>
@endscript
