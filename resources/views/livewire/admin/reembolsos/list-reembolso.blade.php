{{--
    REEMBOLSOS — LISTADO
    --------------------------------------------------------------------------
    Permite consultar las solicitudes de reembolso. Incluye búsqueda, ordenación y paginación.
    Ofrece los filtros disponibles en la pantalla y acceso de solo lectura al detalle.

    Componentes reutilizables utilizados:
    - <x-layout.loader.fullpage />: Indicador global durante operaciones de Livewire.
    - <x-list.actions />: Contenedor del buscador y filtros del listado.
    - <x-list.button-group />: Agrupa las acciones disponibles por registro.
    - <x-list.heading />: Cabecera del módulo con título y acciones.
    - <x-list.search-input />: Buscador reactivo del listado.
    - <x-list.sortable-button />: Control de ordenación por columna.
    - <x-list.status-badge />: Etiqueta visual del estado del registro.
    - <x-list.table />: Contenedor reutilizable para tablas.
    - <x-list.view-button />: Enlace para consultar el detalle del registro.
    - Botón Descargar Excel: Exporta los reembolsos que coinciden con los filtros aplicados.
    --------------------------------------------------------------------------
--}}

@section('title', 'Reembolsos')

<div x-data="listReembolso" class="py-3">

    <x-list.heading>

        <x-slot:title>
            Reembolsos
        </x-slot:title>

    </x-list.heading>

    <x-list.actions>

        <x-slot:search>

            <x-list.search-input wire:model.live.debounce.1200ms="search" />

        </x-slot:search>

        <x-slot:group>

        </x-slot:group>

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

            <label class="form-label" for="listReembolso-status">
                Estado
            </label>

            <select id="listReembolso-status" class="form-select" wire:model.live="status">

                <option value="">Todos</option>
                <option value="pendiente">Pendiente</option>
                <option value="aprobado">Aprobado</option>
                <option value="pagado">Reembolsado</option>
                <option value="rechazado">Rechazado</option>

            </select>

        </div>

        <div class="col-md-6 col-xl-2">

            <label class="form-label" for="listReembolso-from">
                Desde
            </label>
            <input id="listReembolso-from" type="date" class="form-control" wire:model.live="date_from">
        </div>

        <div class="col-md-6 col-xl-2">

            <label class="form-label" for="listReembolso-to">
                Hasta
            </label>
            <input id="listReembolso-to" type="date" class="form-control" wire:model.live="date_to">
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

                <th>Reserva</th>

                <th>Empresa </th>

                <th>Monto
                    <x-list.sortable-button column="monto" :$sortColumn :$sortDirection />
                </th>

                <th>Estado
                    <x-list.sortable-button column="estatus" :$sortColumn :$sortDirection />
                </th>

                <th>Solicitado
                    <x-list.sortable-button column="created_at" :$sortColumn :$sortDirection />
                </th>

                <th></th>

            </tr>
        </thead>

        <tbody>

            @forelse ($reembolsos as $reembolso)
                <tr wire:key="listReembolso-{{ $reembolso->id }}">
                    <td>
                        {{ $reembolso->id }}
                    </td>

                    <td>
                        @if ($reembolso->pagoReserva?->reserva)

                            @if ($canViewReservation)
                                <a href="{{ route('admin.reservas.detail', ['reserva_id' => $reembolso->pagoReserva->reserva->id]) }}"
                                    wire:navigate>
                                    {{ $reembolso->pagoReserva->reserva->codigo_referencia }}
                                </a>
                            @else
                                {{ $reembolso->pagoReserva->reserva->codigo_referencia }}
                            @endif

                        @else
                            —
                        @endif
                    </td>

                    <td>
                        {{ $reembolso->empresa?->nombre ?? '—' }}
                    </td>

                    <td>
                        {{ number_format($reembolso->monto ?? 0, 2) }}
                    </td>

                    <td>
                        <x-list.status-badge :status="$reembolso->estatus" />
                    </td>

                    <td>
                        {{ $reembolso->created_at?->format('d/m/Y H:i') ?? '—' }}
                    </td>

                    <td class="text-end">

                        <x-list.button-group>

                            @if ($canDetail)
                                <x-list.view-button :route="route('admin.reembolsos.detail', ['reembolso_id' => $reembolso->id])" :target="false" />
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

    {{ $reembolsos->links() }}

    <x-layout.loader.fullpage wire:loading.delay.short />

</div>

@script
    <script>
        Alpine.data('listReembolso', () => ({
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


