{{--
    TASA DE SERVICIO — LISTADO
    --------------------------------------------------------------------------
    Permite consultar los rangos y modalidades de cobro del servicio web. Incluye búsqueda,
    ordenación y paginación. Ofrece los filtros disponibles en la pantalla. Presenta las acciones
    de cada registro según las autorizaciones del administrador.

    Componentes reutilizables utilizados:
    - <x-list.heading />: Cabecera del módulo con título y acciones.
    - <x-list.add-button />: Enlace para abrir el formulario de alta.
    - <x-list.actions />: Contenedor del buscador y las acciones del listado.
    - <x-list.search-input />: Buscador vinculado al estado del listado.
    - <x-list.table />: Contenedor reutilizable de la tabla del listado.
    - <x-list.sortable-button />: Control para ordenar por una columna.
    - <x-list.status-badge />: Etiqueta visual del estado del registro.
    - <x-list.button-group />: Agrupación de los botones de acción de una fila.
    - <x-list.status-button />: Botón para solicitar un cambio de estado.
    - <x-list.edit-button />: Enlace para editar el registro.
    - <x-layout.loader.fullpage />: Indicador de carga durante las operaciones de Livewire.
    --------------------------------------------------------------------------
--}}

@section('title', 'Tasa de Servicio')

<div x-data="listTasaServicio" class="py-3">

    <x-list.heading>

        <x-slot:title>
            Tasa de Servicio
        </x-slot:title>

        <x-slot:button>

            @if ($canAdd)
                <x-list.add-button :route="route('admin.tasas-servicio.add')">
                    Nueva tasa
                </x-list.add-button>
            @endif

        </x-slot:button>

    </x-list.heading>

    <p class="text-body-secondary">Monto fijo o porcentaje por pasaje, según su precio final después de descuentos. Los
        límites del rango
        están incluidos.</p>

    <x-list.actions>

        <x-slot:search>

            <x-list.search-input wire:model.live.debounce.1200ms="search" />

        </x-slot:search>

        <x-slot:group>

        </x-slot:group>

    </x-list.actions>

    <div class="row mb-3">

        <div class="col-md-3">

            <label for="tasa-status" class="form-label">
                Estado
            </label>

            <select id="tasa-status" class="form-select" wire:model.live="status">

                <option value="">Todos</option>
                <option value="1">Activo</option>
                <option value="0">Inactivo</option>

            </select>

        </div>

    </div>

    <x-list.table>

        <thead>

            <tr>
                <th>Monto mínimo USD
                    <x-list.sortable-button column="monto_minimo" :$sortColumn :$sortDirection />
                </th>

                <th>Monto máximo USD</th>

                <th>Tipo</th>

                <th>Valor por pasaje</th>

                <th>Estado</th>

                <th class="text-end">Acciones</th>

            </tr>
        </thead>

        <tbody>

            @forelse($tasas as $tasa)
                <tr wire:key="tasa-{{ $tasa->id }}">
                    <td>
                        {{ number_format($tasa->monto_minimo, 2) }}
                    </td>

                    <td>
                        {{ $tasa->monto_maximo === null ? 'Sin límite' : number_format($tasa->monto_maximo, 2) }}
                    </td>

                    <td>
                        {{ $tasa->tipo_servicio === 2 ? 'Porcentaje' : 'Monto fijo' }}
                    </td>

                    <td>
                        {{ number_format($tasa->cantidad, 2) }} {{ $tasa->tipo_servicio === 2 ? '%' : 'USD' }}
                    </td>

                    <td>
                        <x-list.status-badge :status="$tasa->estatus" />
                    </td>

                    <td class="text-end">

                        <x-list.button-group>

                            @if ($canEdit)
                                <x-list.status-button wire:click="changeStatus({{ $tasa->id }})"
                                    :status="$tasa->estatus" />

                                <x-list.edit-button :route="route('admin.tasas-servicio.edit', $tasa->id)" />
                            @endif

                        </x-list.button-group>

                    </td>

            </tr>@empty

                <tr>
                    <td colspan="6" class="text-center py-4">
                        No hay tasas registradas.
                    </td>

                </tr>
            @endforelse

        </tbody>

    </x-list.table>

    {{ $tasas->links() }}

    <x-layout.loader.fullpage wire:loading.delay.short />

</div>

@script
    <script>
        Alpine.data('listTasaServicio', () => ({
            init() {
                this.toastCleanup = [Livewire.on('successEventList', data => this.$store.toast.success(data
                    .message)), Livewire.on(
                    'errorEventList', data => this.$store.toast.info(data.message))];
                const savedMessage = @js(session()->pull('admin_success'));
                if (savedMessage) this.$nextTick(() => Livewire.dispatch('successEventList', {
                    message: savedMessage
                }));
            },
            destroy() {
                this.toastCleanup?.forEach(cleanup => cleanup());
            }
        }));
    </script>
@endscript
