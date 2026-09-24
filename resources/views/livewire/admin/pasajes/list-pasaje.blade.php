{{--
    PASAJES — LISTADO
    --------------------------------------------------------------------------
    Permite consultar los boletos, sus viajeros y la fecha de reserva. Incluye búsqueda, ordenación y paginación.
    Ofrece los filtros disponibles en la pantalla. Presenta las acciones de cada registro según
    las autorizaciones del administrador.

    Componentes reutilizables utilizados:
    - <x-layout.loader.fullpage />: Indicador global durante operaciones de Livewire.
    - <x-list.actions />: Contenedor del buscador y filtros del listado.
    - <x-list.button-group />: Agrupa las acciones disponibles por registro.
    - <x-list.heading />: Cabecera del módulo con título y acciones.
    - <x-list.search-input />: Buscador reactivo del listado.
    - <x-list.sortable-button />: Control de ordenación por columna.
    - <x-list.status-reserva />: Etiqueta del estado de pago de la reserva.
    - <x-list.table />: Contenedor reutilizable para tablas.
    - <x-list.view-button />: Enlace para consultar el detalle del registro.
    --------------------------------------------------------------------------
--}}

@section('title', 'Pasajes')

<div x-data="listPasaje" class="py-3">

    <x-list.heading>

        <x-slot:title>
            Pasajes
        </x-slot:title>

    </x-list.heading>

    <x-list.actions>

        <x-slot:search>

            <x-list.search-input wire:model.live.debounce.1200ms="search" />

        </x-slot:search>

    </x-list.actions>

    <div class="row g-3 mb-3 align-items-end">

        <div class="col-md-6 col-xl-2">

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

        <div class="col-md-6 col-xl-2">

            <label class="form-label" for="filtro-estado-pago">
                Estado de la reserva
            </label>

            <select id="filtro-estado-pago" class="form-select" wire:model.live="status">
                <option value="">Todos los estados</option>
                <option value="{{ \App\Models\Reserva::ESTADO_PAGO_PAGADO }}">Pagadas</option>
                <option value="{{ \App\Models\Reserva::ESTADO_PAGO_PENDIENTE }}">Pendientes</option>
                <option value="{{ \App\Models\Reserva::ESTADO_PAGO_CANCELADO }}">Canceladas</option>
            </select>

        </div>

        <div class="col-md-6 col-xl-2">

            <label class="form-label" for="listPasaje-from">
                Desde
            </label>
            <input id="listPasaje-from" type="date" class="form-control" wire:model.live="date_from">
        </div>

        <div class="col-md-6 col-xl-2">

            <label class="form-label" for="listPasaje-to">
                Hasta
            </label>
            <input id="listPasaje-to" type="date" class="form-control" wire:model.live="date_to">
        </div>

        @if ($canDetail)
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

                <th>Reserva </th>

                <th>Fecha de reserva</th>

                <th>Viajero </th>

                <th>Documento </th>

                <th>Asiento
                    <x-list.sortable-button column="numero_asiento" :$sortColumn :$sortDirection />
                </th>

                <th>Abordado
                    <x-list.sortable-button column="abordado" :$sortColumn :$sortDirection />
                </th>

                <th>Precio
                    <x-list.sortable-button column="precio_base" :$sortColumn :$sortDirection />
                </th>

                <th>Descuento
                    <x-list.sortable-button column="descuento" :$sortColumn :$sortDirection />
                </th>

                <th>Subtotal
                    <x-list.sortable-button column="subtotal" :$sortColumn :$sortDirection />
                </th>

                <th>Tasa de servicio</th>

                <th>Total
                    <x-list.sortable-button column="total" :$sortColumn :$sortDirection />
                </th>

                <th>Pago </th>

                <th></th>

            </tr>
        </thead>

        <tbody>

            @forelse ($pasajes as $pasaje)
                <tr wire:key="listPasaje-{{ $pasaje->id }}">
                    <td>
                        {{ $pasaje->id }}
                    </td>

                    <td>
                        {{ $pasaje->reserva?->codigo_referencia ?? '—' }}
                    </td>

                    <td class="text-nowrap">
                        {{ $pasaje->reserva?->fecha_compra?->format('d/m/Y H:i') ?? '—' }}
                    </td>
                    <td>
                        {{ $pasaje->viajero?->nombre ?? '—' }}
                    </td>

                    <td>
                        {{ $pasaje->viajero?->documento_identidad ?? '—' }}
                    </td>

                    <td>
                        {{ $pasaje->numero_asiento ?? '—' }}
                    </td>

                    <td>
                        {{ $pasaje->abordado ? 'Sí' : 'No' }}
                    </td>

                    <td>
                        {{ number_format($pasaje->precio_base, 2) }}
                    </td>

                    <td>
                        {{ number_format($pasaje->descuento, 2) }}
                    </td>

                    <td>
                        {{ number_format($pasaje->subtotal, 2) }}
                    </td>

                    <td>
                        {{ number_format($pasaje->tasa_servicio, 2) }}
                    </td>

                    <td>
                        {{ number_format($pasaje->total, 2) }}
                    </td>

                    <td>
                        <x-list.status-reserva :status="$pasaje->reserva->estado_pago" />
                    </td>

                    <td class="text-end">

                        <x-list.button-group>

                            @if ($canDetail)
                                <x-list.view-button :route="route('admin.pasajes.detail', ['pasaje_id' => $pasaje->id])" :target="false" />
                            @endif

                        </x-list.button-group>

                    </td>

                </tr>

            @empty

                <tr>
                    <td colspan="14" class="text-center py-5">
                        No se encontraron registros.
                    </td>

                </tr>
            @endforelse

        </tbody>

    </x-list.table>

    {{ $pasajes->links() }}

    <x-layout.loader.fullpage wire:loading.delay.short />

</div>

@script
    <script>
        Alpine.data('listPasaje', () => ({
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


