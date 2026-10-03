{{--
    PASAJES — LISTADO
    --------------------------------------------------------------------------
    Permite consultar los boletos, sus viajeros y la fecha de reserva. Incluye búsqueda, ordenación y paginación.
    Ofrece los filtros disponibles en la pantalla. Presenta las acciones de cada registro según
    las autorizaciones del usuario empresarial.

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

            <label class="form-label" for="filtro-estado-pago">
                Estado de la reserva
            </label>

            <select id="filtro-estado-pago" class="form-select" wire:model.live="status">
                <option value="">Todos</option>
                <option value="{{ \App\Models\Reserva::ESTADO_PAGO_PAGADO }}">Pagada y Reembolsado</option>
                <option value="{{ \App\Models\Reserva::ESTADO_PAGO_PENDIENTE }}">Pendiente</option>
                <option value="{{ \App\Models\Reserva::ESTADO_PAGO_CANCELADO }}">Cancelada</option>
                <option value="{{ \App\Models\Reserva::ESTADO_PAGO_REPROGRAMADO }}">Reprogramada</option>
                <option value="{{ \App\Models\Reserva::ESTADO_PAGO_REEMBOLSADO }}">Reembolsada</option>

            </select>

        </div>

        <div class="col-md-6 col-xl-2">

            <label class="form-label" for="listPasaje-from">
                Desde
            </label>
            <input id="listPasaje-from" type="date" class="form-control" wire:model.live="date_from"
                min="{{ $this->getMinFilterDate() }}" max="{{ $this->getMaxFilterDate() }}">
        </div>

        <div class="col-md-6 col-xl-2">

            <label class="form-label" for="listPasaje-to">
                Hasta
            </label>
            <input id="listPasaje-to" type="date" class="form-control" wire:model.live="date_to"
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

                <th class="text-nowrap">Reserva</th>

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

                <th>{{ $mostrarTasaServicio ? 'Subtotal' : 'Total' }}
                    <x-list.sortable-button column="subtotal" :$sortColumn :$sortDirection />
                </th>

                @if ($mostrarTasaServicio)
                    <th>Tasa de servicio</th>

                    <th>Total
                        <x-list.sortable-button column="total" :$sortColumn :$sortDirection />
                    </th>
                @endif

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

                    <td class="text-nowrap">
                        {{ $pasaje->reserva?->codigo_referencia ?? '—' }}
                    </td>

                    <td class="text-nowrap">
                        {{ $pasaje->reserva?->fecha_compra?->format('d/m/Y H:i') ?? '—' }}
                    </td>
                    <td>
                        {{ $pasaje->viajero_nombre_completo ?: '—' }}
                    </td>

                    <td>
                        {{ $pasaje->viajero_documento ?? '—' }}
                    </td>

                    <td>
                        {{ $pasaje->numero_asiento ?? '—' }}
                    </td>

                    <td>
                        {{ $pasaje->abordado ? 'Sí' : 'No' }}
                    </td>

                    <td>
                        <x-money.dual :usd="$pasaje->precio_base" :bs="$pasaje->calcularMontoBs($pasaje->precio_base)" />
                    </td>

                    <td>
                        <x-money.dual :usd="$pasaje->descuento" :bs="$pasaje->calcularMontoBs($pasaje->descuento)" />
                    </td>

                    <td>
                        <x-money.dual :usd="$pasaje->subtotal" :bs="$pasaje->calcularMontoBs($pasaje->subtotal)" />
                    </td>

                    @if ($mostrarTasaServicio)
                        <td>
                            <x-money.dual :usd="$pasaje->tasa_servicio" :bs="$pasaje->calcularMontoBs($pasaje->tasa_servicio)" />
                        </td>

                        <td>
                            <x-money.dual :usd="$pasaje->total" :bs="$pasaje->calcularMontoBs($pasaje->total)" />
                        </td>
                    @endif

                    <td>
                        <x-list.status-reserva :status="$pasaje->reserva->estado_pago" />
                    </td>

                    <td class="text-end">

                        <x-list.button-group>
                            
                            @if ($canDetail)
                                <x-list.view-button :route="route('empresas.pasajes.detail', ['pasaje_id' => $pasaje->id])" :target="false" />
                            @endif

                        </x-list.button-group>

                    </td>

                </tr>

            @empty

                <tr>
                    <td colspan="{{ $mostrarTasaServicio ? 14 : 12 }}" class="text-center py-5">
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
                const savedMessage = @js(session()->pull('empresa_success'));
                if (savedMessage) this.$nextTick(() => Livewire.dispatch('successEventList', {
                    message: savedMessage
                }));
            },

        }));
    </script>
@endscript
