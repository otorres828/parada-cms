{{--
    CAMPAÑAS — LISTADO
    --------------------------------------------------------------------------
    Permite consultar las campañas promocionales. Incluye búsqueda, ordenación y paginación.
    Ofrece los filtros disponibles en la pantalla. Presenta las acciones de cada registro según
    las autorizaciones del administrador.

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
    - <x-list.view-button />: Enlace para consultar el detalle del registro.
    --------------------------------------------------------------------------
--}}

@section('title', 'Campañas')

<div x-data="listCampana" class="py-3">

    <x-list.heading>

        <x-slot:title>
            Campañas
        </x-slot:title>

    </x-list.heading>

    <x-list.actions>

        <x-slot:search>

            <x-list.search-input wire:model.live.debounce.1200ms="search" />

        </x-slot:search>

        <x-slot:group>

            <select id="listCampana-status" class="form-select" style="width: 180px; max-width: 100%;" wire:model.live="status" aria-label="Filtrar por estado">

                <option value="">Todos los estados</option>
                <option value="1">Activo</option>
                <option value="0">Inactivo</option>

            </select>

            <div class="d-flex align-items-center gap-2">

                <input id="listCampana-from" type="date" class="form-control" style="width: 160px;" wire:model.live="date_from" aria-label="Fecha inicial"
                    min="{{ $this->getMinFilterDate() }}" max="{{ $this->getMaxFilterDate() }}">

                <span class="text-body-secondary" aria-hidden="true">a</span>

                <input id="listCampana-to" type="date" class="form-control" style="width: 160px;" wire:model.live="date_to" aria-label="Fecha final"
                    min="{{ $this->getMinFilterDate() }}" max="{{ $this->getMaxFilterDate() }}">

            </div>

        </x-slot:group>

        @if (Route::has('admin.cupones.add') && $canAdd)

            <x-slot:button>

                <x-list.add-button :route="route('admin.cupones.add')" >
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

                <th>Campaña
                    <x-list.sortable-button column="nombre_campana" :$sortColumn :$sortDirection />
                </th>

                <th>Empresa </th>

                <th>Descuento
                    <x-list.sortable-button column="tipo_descuento" :$sortColumn :$sortDirection />
                </th>

                <th>Modalidad
                    <x-list.sortable-button column="modalidad" :$sortColumn :$sortDirection />
                </th>

                <th>Aplica en
                    <x-list.sortable-button column="aplica_en" :$sortColumn :$sortDirection />
                </th>

                <th>Valor
                    <x-list.sortable-button column="monto_descuento" :$sortColumn :$sortDirection />
                </th>

                <th>Vencimiento
                    <x-list.sortable-button column="fecha_fin" :$sortColumn :$sortDirection />
                </th>

                <th>Estado
                    <x-list.sortable-button column="estatus" :$sortColumn :$sortDirection />
                </th>

                <th></th>

            </tr>
        </thead>

        <tbody>

            @forelse ($cupones as $configuracionCupon)
                <tr wire:key="listCampana-{{ $configuracionCupon->id }}">
                    <td>
                        {{ $configuracionCupon->id }}
                    </td>

                    <td>
                        {{ $configuracionCupon->nombre_campana ?? '—' }}
                    </td>

                    <td>
                        {{ $configuracionCupon->empresa?->nombre ?? '—' }}
                    </td>

                    <td>
                        {{ $configuracionCupon->tipo_descuento === 'porcentaje' ? 'Porcentaje' : 'Monto fijo' }}
                    </td>

                    <td>
                        {{ match ($configuracionCupon->modalidad) {
                            'PRIMERA_COMPRA' => 'Primera compra',
                            'USUARIO_NUEVO' => 'Usuario nuevo',
                            default => 'General',
                        } }}
                    </td>

                    <td>
                        {{ $configuracionCupon->aplica_en === 'pasajes' ? 'Cada pasaje' : 'Reserva general' }}
                    </td>

                    <td>
                        {{ number_format($configuracionCupon->monto_descuento ?? 0, 2) }}
                    </td>

                    <td>
                        {{ $configuracionCupon->fecha_fin?->format('d/m/Y H:i') ?? '—' }}
                    </td>

                    <td>

                        <x-list.status-badge :status="$configuracionCupon->estatus" />

                    </td>

                    <td class="text-end">

                        <x-list.button-group>

                            @if ($canDetail)

                                <x-list.view-button :route="route('admin.cupones.detail', [
                                    'configuracion_cupon_id' => $configuracionCupon->id
                                ])" :target="false" />

                            @endif

                            @if ($canEdit)

                                <x-list.edit-button :route="route('admin.cupones.edit', [
                                    'configuracion_cupon_id' => $configuracionCupon->id
                                ])" />

                            @endif

                            @if ($canEdit)

                                <x-list.status-button wire:click="changeStatus({{ $configuracionCupon->id }})" :status="$configuracionCupon->estatus" wire:loading.attr="disabled" />

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

    {{ $cupones->links() }}

    <x-layout.loader.fullpage wire:loading.delay.short />

</div>

@script
    <script>
        Alpine.data('listCampana', () => ({
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
