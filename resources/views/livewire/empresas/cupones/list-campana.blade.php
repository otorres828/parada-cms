{{--
    CAMPAÑAS — LISTADO
    --------------------------------------------------------------------------
    Permite consultar las campañas promocionales. Incluye búsqueda, ordenación y paginación.
    Ofrece los filtros disponibles en la pantalla. Presenta las acciones de cada registro según
    las autorizaciones del usuario empresarial.

    Componentes reutilizables utilizados:
    - <x-list.heading />: Elemento de presentación del listado.
    - <x-slot />: Elemento de presentación del listado.
    - <x-list.actions />: Elemento de presentación del listado.
    - <x-list.search-input />: Elemento de presentación del listado.
    - <x-list.table />: Tabla propia del panel.
    - <x-list.sortable-button />: Ordenación de columnas.
    - <x-list.status-badge />: Elemento de presentación del listado.
    - <x-list.add-button />: Acceso al alta de campañas según permiso.
    - <x-list.edit-button />: Edición de campañas propias.
    - <x-list.button-group />: Elemento de presentación del listado.
    - <x-list.status-button />: Elemento de presentación del listado.
    - <x-layout.loader.fullpage />: Elemento de presentación del listado.
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
                <option value="2">Inactivo</option>

            </select>

            <div class="d-flex align-items-center gap-2">

                <x-form.date-input
                    id="listCampana-from"
                    class="form-control"
                    style="width: 160px;"
                    wire:model.live="date_from"
                    aria-label="Fecha inicial"
                    min="{{ $this->getMinFilterDate() }}"
                    max="{{ $this->getMaxFilterDate() }}"
                />

                <span class="text-body-secondary" aria-hidden="true">a</span>

                <x-form.date-input
                    id="listCampana-to"
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

                <x-list.add-button :route="route('empresas.cupones.add')">
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
                        {{ $configuracionCupon->tipo_descuento === 'porcentaje' ? 'Porcentaje' : 'Monto fijo' }}
                    </td>

                    <td>
                        {{ match ($configuracionCupon->modalidad) {
                            'PRIMERA_COMPRA' => 'Primera compra',
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

                            @if ($canEdit)

                                <x-list.edit-button :route="route('empresas.cupones.edit', ['configuracion_cupon_id' => $configuracionCupon->id])" :target="false" />
                            <x-list.status-button wire:click="changeStatus({{ $configuracionCupon->id }})" :status="$configuracionCupon->estatus" wire:loading.attr="disabled" />

                            @endif

                        </x-list.button-group>

                    </td>

                </tr>

            @empty

                <tr>
                    <td colspan="9" class="text-center py-5">
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
                const savedMessage = @js(session()->pull('empresas_campana_success'));
                if (savedMessage) this.$nextTick(() => this.$store.toast.success(savedMessage));
                this.toastCleanup = [
                    Livewire.on('empresas_campana_success', data => this.$store.toast.success(data.message)),
                    Livewire.on('empresas_campana_error', data => this.$store.toast.info(data.message)),
                ];
            },

        }));
    </script>
@endscript
