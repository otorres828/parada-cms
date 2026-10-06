{{--
    REPROGRAMACIONES — LISTADO
    --------------------------------------------------------------------------
    Permite consultar las reprogramaciones por cliente, con búsqueda y filtros por
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

@section('title', 'Reprogramaciones')

<div x-data="listReprogramacion" class="py-3">

    <x-list.heading>

        <x-slot:title>
            Reprogramaciones
        </x-slot:title>

    </x-list.heading>

    <x-list.actions>

        <x-slot:search>

            <x-list.search-input wire:model.live.debounce.1200ms="search" />

        </x-slot:search>

        <x-slot:group>

            <div class="d-flex align-items-center gap-2">

                <x-form.date-input
                    id="listReprogramacion-from"
                    class="form-control"
                    style="width: 160px;"
                    wire:model.live="date_from"
                    aria-label="Fecha inicial"
                    min="{{ $this->getMinFilterDate() }}"
                    max="{{ $this->getMaxFilterDate() }}"
                />

                <span class="text-body-secondary" aria-hidden="true">a</span>

                <x-form.date-input
                    id="listReprogramacion-to"
                    class="form-control"
                    style="width: 160px;"
                    wire:model.live="date_to"
                    aria-label="Fecha final"
                    min="{{ $this->getMinFilterDate() }}"
                    max="{{ $this->getMaxFilterDate() }}"
                />

            </div>

        </x-slot:group>

        @if ($canAdd)
            <x-slot:button>

                <x-list.add-button :route="route('empresas.reprogramaciones.add')">
                    Nuevo registro
                </x-list.add-button>

            </x-slot:button>
        @endif

    </x-list.actions>

    <x-list.table>

        <thead>

            <tr>
                
                <th>ID
                    <x-list.sortable-button column="id" :$sortColumn :$sortDirection />
                </th>

                <th>Reserva anterior</th>

                <th>Referencia
                    <x-list.sortable-button column="codigo_referencia" :$sortColumn :$sortDirection />
                </th>

                <th>Cliente </th>

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

                <tr wire:key="listReprogramacion-{{ $reserva->id }}">

                     <td>
                        {{ $reserva->id }}
                    </td>
                    
                    <td>
                        {{ $reserva->reservaOriginal?->codigo_referencia ?? $reserva->reprogramacion_id }}
                    </td>

                    <td>
                        {{ $reserva->codigo_referencia ?? '—' }}
                        @if ($reserva->isTaquilla())
                            <span class="badge text-bg-info">Taquilla</span>
                        @endif
                    </td>

                    <td>
                        {{ $reserva->nombre_comprador }}
                    </td>

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

                        <x-money.dual :usd="$reserva->getMontoTotalUSD($viewTasaServicio)" :bs="$reserva->getMontoTotalBS($viewTasaServicio)" />

                    </td>

                    <td>

                        <x-list.status-reserva :status="$reserva->estado_pago" />

                    </td>

                    <td class="text-end">

                        <x-list.button-group>

                            @if ($canDetail)
                                <a class="btn btn-outline-secondary"
                                    href="{{ route('empresas.reservas.detail', ['reserva_id' => $reserva->id]) }}"
                                    wire:navigate title="Detalle" aria-label="Detalle"><i
                                        class="bi bi-people-fill"></i></a>
                            @endif

                        </x-list.button-group>

                    </td>

                </tr>

            @empty

                <tr>
                    <td colspan="11" class="text-center py-5">
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
        Alpine.data('listReprogramacion', () => ({
            destroy() {
                this.toastCleanup?.forEach(cleanup => cleanup());
            },
            init() {
                const savedMessage = @js(session()->pull('empresas_reprogramacion_success'));
                if (savedMessage) this.$nextTick(() => this.$store.toast.success(savedMessage));
                this.toastCleanup = [
                    Livewire.on('empresas_reprogramacion_success', data => this.$store.toast.success(data.message)),
                    Livewire.on('empresas_reprogramacion_error', data => this.$store.toast.info(data.message)),
                ];
            },

        }));
    </script>
@endscript
