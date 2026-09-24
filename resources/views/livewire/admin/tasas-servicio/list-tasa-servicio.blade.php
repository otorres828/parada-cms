{{--
    TASA DE SERVICIO — LISTADO
    --------------------------------------------------------------------------
    Permite consultar los rangos y modalidades de cobro del servicio web. Incluye búsqueda,
    ordenación y paginación. Ofrece los filtros disponibles en la pantalla. Presenta las acciones
    de cada registro según las autorizaciones del administrador.

    Componentes reutilizables utilizados:
    - <x-layout.loader.fullpage />: Indicador global durante operaciones de Livewire.
    - <x-list.actions />: Contenedor del buscador y filtros del listado.
    - <x-list.add-button />: Botón para registrar un nuevo elemento.
    - <x-list.button-group />: Agrupa las acciones disponibles por registro.
    - <x-list.edit-button />: Enlace para editar el registro.
    - <x-list.heading />: Cabecera del módulo con título y acciones.
    - <x-list.search-input />: Buscador reactivo del listado.
    - <x-list.sortable-button />: Control de ordenación por columna.
    - <x-list.status-badge />: Etiqueta visual del estado del registro.
    - <x-list.status-button />: Acción para cambiar el estado del registro.
    - <x-list.table />: Contenedor reutilizable para tablas.
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
                <th>Monto mínimo
                    <x-list.sortable-button column="monto_minimo" :$sortColumn :$sortDirection />
                </th>

                <th>Monto máximo</th>

                <th>Tipo</th>

                <th>Valor por pasaje</th>

                <th>Estado</th>

                <th></th>

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
                        {{ number_format($tasa->cantidad, 2) }} {{ $tasa->tipo_servicio === 2 ? '%' : '' }}
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


