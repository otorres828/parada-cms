{{--
    RESERVAS Y VENTAS — LISTADO
    --------------------------------------------------------------------------
    Permite consultar las compras por cliente, empresa y estado de pago, con búsqueda, filtros por
    empresa y fechas, ordenación y paginación. Muestra el origen y destino propios de cada
    reserva, su importe y el acceso al detalle según los permisos.

    Componentes reutilizables utilizados:
    - <x-list.heading />: Cabecera del módulo con título y acciones.
    - <x-list.actions />: Contenedor del buscador y las acciones del listado.
    - <x-list.search-input />: Buscador vinculado al estado del listado.
    - <x-list.table />: Contenedor reutilizable de la tabla del listado.
    - <x-list.sortable-button />: Control para ordenar por una columna.
    - <x-list.status-reserva />: Etiqueta del estado de pago según las constantes de Reserva.
    - <x-list.button-group />: Agrupación de los botones de acción de una fila.
    - <x-list.view-button />: Enlace para consultar el detalle del registro.
    - <x-layout.loader.fullpage />: Indicador de carga durante las operaciones de Livewire.
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

    <div class="row g-3 mb-3">

        <div class="col-md-3">

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

        <div class="col-md-3">

            <label class="form-label" for="listReserva-status">
                Estado
            </label>

            <select id="listReserva-status" class="form-select" wire:model.live="status">

                <option value="">Todos</option>
                <option value="{{ \App\Models\Reserva::ESTADO_PAGO_PAGADO }}">Pagada</option>
                <option value="{{ \App\Models\Reserva::ESTADO_PAGO_PENDIENTE }}">Pendiente</option>
                <option value="{{ \App\Models\Reserva::ESTADO_PAGO_CANCELADO }}">Cancelada</option>
                <option value="{{ \App\Models\Reserva::ESTADO_PAGO_REEMBOLSADO }}">Reembolsada</option>

            </select>

        </div>

        <div class="col-md-3">

            <label class="form-label" for="listReserva-from">
                Desde
            </label>
            <input id="listReserva-from" type="date" class="form-control" wire:model.live="date_from">
        </div>

        <div class="col-md-3">

            <label class="form-label" for="listReserva-to">
                Hasta
            </label>
            <input id="listReserva-to" type="date" class="form-control" wire:model.live="date_to">
        </div>

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

                <th>Total USD
                    <x-list.sortable-button column="monto_total" :$sortColumn :$sortDirection />
                </th>

                <th>Estado
                    <x-list.sortable-button column="estado_pago" :$sortColumn :$sortDirection />
                </th>

                <th class="text-end">Acciones</th>

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
                        {{ number_format($reserva->monto_total ?? 0, 2) }}
                    </td>

                    <td>
                        <x-list.status-reserva :status="$reserva->estado_pago" />
                    </td>

                    <td class="text-end">

                        <x-list.button-group>

                            @if ($canDetail)
                                <x-list.view-button :route="route('admin.reservas.detail', ['reserva_id' => $reserva->id])" :target="false" />
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
