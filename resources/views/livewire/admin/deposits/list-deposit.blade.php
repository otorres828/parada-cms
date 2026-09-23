{{--
    PAGOS RECIBIDOS — LISTADO
    --------------------------------------------------------------------------
    Permite consultar los pagos en el listado de referencia. Incluye búsqueda, ordenación y
    paginación. Ofrece los filtros disponibles en la pantalla. Presenta las acciones de cada
    registro según las autorizaciones del administrador.

    Componentes reutilizables utilizados:
    - <x-list.heading />: Cabecera del módulo con título y acciones.
    - <x-list.add-button />: Enlace para abrir el formulario de alta.
    - <x-list.actions />: Contenedor del buscador y las acciones del listado.
    - <x-list.search-input />: Buscador vinculado al estado del listado.
    - <x-list.table />: Contenedor reutilizable de la tabla del listado.
    - <x-list.sortable-button />: Control para ordenar por una columna.
    - <x-list.button-group />: Agrupación de los botones de acción de una fila.
    - <x-list.view-button />: Enlace para consultar el detalle del registro.
    - <x-layout.loader.fullpage />: Indicador de carga durante las operaciones de Livewire.
    --------------------------------------------------------------------------
--}}

@section('title', 'Pagos recibidos')

<div x-data="listDeposit" class="py-3">

    <x-list.heading>

        <x-slot:title>
            Pagos recibidos
        </x-slot:title>

        <x-slot:button>

            @if (Route::has('admin.pagos.add') && $canAdd)
                <x-list.add-button :route="route('admin.pagos.add')">
                    Nuevo registro
                </x-list.add-button>
            @endif

        </x-slot:button>

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

            <label class="form-label" for="listDeposit-from">
                Desde
            </label>
            <input id="listDeposit-from" type="date" class="form-control" wire:model.live="date_from">
        </div>

        <div class="col-md-3">

            <label class="form-label" for="listDeposit-to">
                Hasta
            </label>
            <input id="listDeposit-to" type="date" class="form-control" wire:model.live="date_to">
        </div>

    </div>

    <p class="text-body-secondary">Conciliación de pagos recibidos. Registrar el pago acredita el neto de la empresa.
    </p>

    <x-list.table>

        <thead>

            <tr>
                <th>ID
                    <x-list.sortable-button column="id" :$sortColumn :$sortDirection />
                </th>

                <th>Referencia
                    <x-list.sortable-button column="referencia" :$sortColumn :$sortDirection />
                </th>

                <th>Reserva </th>

                <th>Empresa </th>

                <th>Recibido USD
                    <x-list.sortable-button column="monto" :$sortColumn :$sortDirection />
                </th>

                <th>Neto empresa USD
                    <x-list.sortable-button column="neto_empresa" :$sortColumn :$sortDirection />
                </th>

                <th>Fecha
                    <x-list.sortable-button column="fecha_pago" :$sortColumn :$sortDirection />
                </th>

                <th></th>

            </tr>
        </thead>

        <tbody>

            @forelse ($pagos as $pago)
                <tr wire:key="listDeposit-{{ $pago->id }}">
                    <td>
                        {{ $pago->id }}
                    </td>

                    <td>
                        {{ $pago->referencia ?? '—' }}
                    </td>

                    <td>
                        {{ $pago->reserva?->codigo_referencia ?? '—' }}
                    </td>

                    <td>
                        {{ $pago->empresa?->nombre ?? '—' }}
                    </td>

                    <td>
                        {{ number_format($pago->monto ?? 0, 2) }}
                    </td>

                    <td>
                        {{ number_format($pago->neto_empresa ?? 0, 2) }}
                    </td>

                    <td>
                        {{ $pago->fecha_pago?->format('d/m/Y H:i') ?? '—' }}
                    </td>

                    <td class="text-end">

                        <x-list.button-group>

                            @if (Route::has('admin.pagos.detail') && $canDetail)
                                <x-list.view-button :route="route('admin.pagos.detail', ['pago_id' => $pago->id])" :target="false" />
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

    {{ $pagos->links() }}

    <x-layout.loader.fullpage wire:loading.delay.short />

</div>

@script
    <script>
        Alpine.data('listDeposit', () => ({
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
