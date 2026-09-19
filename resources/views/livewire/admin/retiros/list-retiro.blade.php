{{--
    RETIROS — LISTADO
    --------------------------------------------------------------------------
    Permite consultar las solicitudes de retiro. Incluye búsqueda, ordenación y paginación. Ofrece
    los filtros disponibles en la pantalla. Presenta las acciones de cada registro según las
    autorizaciones del administrador.

    Componentes reutilizables utilizados:
    - <x-list.heading />: Cabecera del módulo con título y acciones.
    - <x-list.add-button />: Enlace para abrir el formulario de alta.
    - <x-list.actions />: Contenedor del buscador y las acciones del listado.
    - <x-list.search-input />: Buscador vinculado al estado del listado.
    - <x-list.table />: Contenedor reutilizable de la tabla del listado.
    - <x-list.sortable-button />: Control para ordenar por una columna.
    - <x-list.status-badge />: Etiqueta visual del estado del registro.
    - <x-list.button-group />: Agrupación de los botones de acción de una fila.
    - <x-list.view-button />: Enlace para consultar el detalle del registro.
    - <x-layout.loader.fullpage />: Indicador de carga durante las operaciones de Livewire.
    --------------------------------------------------------------------------
--}}

@section('title', 'Retiros')

<div x-data="listRetiro" class="py-3">

    <x-list.heading>

        <x-slot:title>
            Retiros
        </x-slot:title>

        <x-slot:button>

            @if (Route::has('admin.retiros.add') && $canAdd)
                <x-list.add-button :route="route('admin.retiros.add')">
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

            <label class="form-label" for="listRetiro-status">
                Estado
            </label>

            <select id="listRetiro-status" class="form-select" wire:model.live="status">

                <option value="">Todos</option>
                <option value="pendiente">Pendiente</option>
                <option value="aprobado">Aprobado</option>
                <option value="pagado">Transferido</option>
                <option value="rechazado">Rechazado</option>

            </select>

        </div>

        <div class="col-md-3">

            <label class="form-label" for="listRetiro-from">
                Desde
            </label>
            <input id="listRetiro-from" type="date" class="form-control" wire:model.live="date_from">
        </div>

        <div class="col-md-3">

            <label class="form-label" for="listRetiro-to">
                Hasta
            </label>
            <input id="listRetiro-to" type="date" class="form-control" wire:model.live="date_to">
        </div>

    </div>

    <x-list.table>

        <thead>

            <tr>
                <th>ID
                    <x-list.sortable-button column="id" :$sortColumn :$sortDirection />
                </th>

                <th>Empresa </th>

                <th>Monto USD
                    <x-list.sortable-button column="monto" :$sortColumn :$sortDirection />
                </th>

                <th>Estado
                    <x-list.sortable-button column="estatus" :$sortColumn :$sortDirection />
                </th>

                <th>Solicitado
                    <x-list.sortable-button column="created_at" :$sortColumn :$sortDirection />
                </th>

                <th>Referencia
                    <x-list.sortable-button column="referencia" :$sortColumn :$sortDirection />
                </th>

                <th class="text-end">Acciones</th>

            </tr>
        </thead>

        <tbody>

            @forelse ($retiros as $retiro)
                <tr wire:key="listRetiro-{{ $retiro->id }}">
                    <td>
                        {{ $retiro->id }}
                    </td>

                    <td>
                        {{ $retiro->empresa?->nombre ?? '—' }}
                    </td>

                    <td>
                        {{ number_format($retiro->monto ?? 0, 2) }}
                    </td>

                    <td>
                        <x-list.status-badge :status="$retiro->estatus" />
                    </td>

                    <td>
                        {{ $retiro->created_at?->format('d/m/Y H:i') ?? '—' }}
                    </td>

                    <td>
                        {{ $retiro->referencia ?? '—' }}
                    </td>

                    <td class="text-end">

                        <x-list.button-group>

                            @if ($capabilities['detail'])
                                <x-list.view-button :route="route('admin.retiros.detail', ['retiro_id' => $retiro->id])" :target="false" />
                            @endif

                            @if ($capabilities['review'])
                                <a class="btn btn-outline-secondary"
                                    href="{{ route('admin.retiros.review', ['retiro_id' => $retiro->id]) }}"
                                    wire:navigate title="Revisar"
                                    aria-label="Revisar"><i class="bi bi-clipboard-check-fill"></i></a>
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

    {{ $retiros->links() }}

    <x-layout.loader.fullpage wire:loading.delay.short />

</div>

@script
    <script>
        Alpine.data('listRetiro', () => ({
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
