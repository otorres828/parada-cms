{{--
    MOVIMIENTOS CONTABLES — LISTADO
    --------------------------------------------------------------------------
    Permite consultar los movimientos financieros. Incluye búsqueda, ordenación y paginación.
    Ofrece los filtros disponibles en la pantalla. Presenta las acciones de cada registro según
    las autorizaciones del administrador.

    Componentes reutilizables utilizados:
    - <x-list.heading />: Cabecera del módulo con título y acciones.
    - <x-list.add-button />: Enlace para abrir el formulario de alta.
    - <x-list.actions />: Contenedor del buscador y las acciones del listado.
    - <x-list.search-input />: Buscador vinculado al estado del listado.
    - <x-list.table />: Contenedor reutilizable para la tabla del listado.
    - <x-list.sortable-button />: Control para ordenar por una columna.
    - <x-list.button-group />: Agrupación de botones de acción de una fila.
    - <x-list.view-button />: Enlace para consultar el detalle del registro.
    - <x-layout.loader.fullpage />: Indicador de carga durante las operaciones de Livewire.
    --------------------------------------------------------------------------
--}}

@section('title', 'Movimientos contables')

<div x-data="listMovimiento" class="py-3">

    <x-list.heading>

        <x-slot:title>
            Movimientos contables
        </x-slot:title>

        <x-slot:button>

            @if (Route::has('admin.movimientos.add') && $canAdd)

                <x-list.add-button :route="route('admin.movimientos.add')">
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

            <label class="form-label" for="listMovimiento-from">
                Desde
            </label>
            <input id="listMovimiento-from" type="date" class="form-control" wire:model.live="date_from">
        </div>

        <div class="col-md-3">

            <label class="form-label" for="listMovimiento-to">
                Hasta
            </label>
            <input id="listMovimiento-to" type="date" class="form-control" wire:model.live="date_to">
        </div>

    </div>

    <x-list.table>

        <thead>

            <tr>
                <th>ID
                    <x-list.sortable-button column="id" :$sortColumn :$sortDirection />
                </th>

                <th>Empresa </th>

                <th>Concepto
                    <x-list.sortable-button column="tipo" :$sortColumn :$sortDirection />
                </th>

                <th>Importe USD
                    <x-list.sortable-button column="monto" :$sortColumn :$sortDirection />
                </th>

                <th>Descripción
                    <x-list.sortable-button column="descripcion" :$sortColumn :$sortDirection />
                </th>

                <th>Fecha
                    <x-list.sortable-button column="created_at" :$sortColumn :$sortDirection />
                </th>

                <th class="text-end">Acciones</th>

            </tr>
        </thead>

        <tbody>

            @forelse ($movimientos as $movimiento)

                <tr wire:key="listMovimiento-{{ $movimiento->id }}">
                    <td>
                        {{ $movimiento->id }}
                    </td>

                    <td>
                        {{ $movimiento->empresa?->nombre ?? '—' }}
                    </td>

                    <td>
                        {{ $movimiento->tipo ?? '—' }}
                    </td>

                    <td>
                        {{ number_format($movimiento->monto ?? 0, 2) }}
                    </td>

                    <td>
                        {{ $movimiento->descripcion ?? '—' }}
                    </td>

                    <td>
                        {{ $movimiento->created_at?->format('d/m/Y H:i') ?? '—' }}
                    </td>

                    <td class="text-end">

                        <x-list.button-group>

                            @if ($capabilities['detail'])

                                <x-list.view-button :route="route('admin.movimientos.detail', ['movimiento_id' => $movimiento->id])" :target="false" />

                            @endif

                        </x-list.button-group>

                    </td>

                </tr>

            @empty

                <tr>
                    <td colspan="7" class="text-center py-5">
                        No se encontraron registros.
                    </td>

                </tr>

            @endforelse

        </tbody>

    </x-list.table>

    {{ $movimientos->links() }}

    <x-layout.loader.fullpage wire:loading.delay.short />

</div>

@script
    <script>
        Alpine.data('listMovimiento', () => ({
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
