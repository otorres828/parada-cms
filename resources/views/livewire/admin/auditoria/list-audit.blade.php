{{--
    AUDITORÍA — LISTADO
    --------------------------------------------------------------------------
    Permite consultar las acciones administrativas registradas. Incluye búsqueda, ordenación y
    paginación. Ofrece los filtros disponibles en la pantalla. Presenta las acciones de cada
    registro según las autorizaciones del administrador.

    Componentes reutilizables utilizados:
    - <x-layout.loader.fullpage />: Indicador global durante operaciones de Livewire.
    - <x-list.actions />: Contenedor del buscador y filtros del listado.
    - <x-list.button-group />: Agrupa las acciones disponibles por registro.
    - <x-list.heading />: Cabecera del módulo con título y acciones.
    - <x-list.search-input />: Buscador reactivo del listado.
    - <x-list.sortable-button />: Control de ordenación por columna.
    - <x-list.table />: Contenedor reutilizable para tablas.
    - <x-list.view-button />: Enlace para consultar el detalle del registro.
    --------------------------------------------------------------------------
--}}

@section('title', 'Auditoría')

<div x-data="listAudit" class="py-3">

    <x-list.heading>

        <x-slot:title>
            Auditoría
        </x-slot:title>


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

            <label class="form-label" for="listAudit-from">
                Desde
            </label>
            <input id="listAudit-from" type="date" class="form-control" wire:model.live="date_from">
        </div>

        <div class="col-md-3">

            <label class="form-label" for="listAudit-to">
                Hasta
            </label>
            <input id="listAudit-to" type="date" class="form-control" wire:model.live="date_to">
        </div>

    </div>

    <x-list.table>

        <thead>

            <tr>
                <th>ID
                    <x-list.sortable-button column="id" :$sortColumn :$sortDirection />
                </th>

                <th>Administrador </th>

                <th>Acción
                    <x-list.sortable-button column="accion" :$sortColumn :$sortDirection />
                </th>

                <th>Entidad
                    <x-list.sortable-button column="entidad" :$sortColumn :$sortDirection />
                </th>

                <th>Registro
                    <x-list.sortable-button column="entidad_id" :$sortColumn :$sortDirection />
                </th>

                <th>Fecha
                    <x-list.sortable-button column="created_at" :$sortColumn :$sortDirection />
                </th>

                <th></th>

            </tr>
        </thead>

        <tbody>

            @forelse ($auditorias as $auditoria)

                <tr wire:key="listAudit-{{ $auditoria->id }}">
                    
                    <td>
                        {{ $auditoria->id }}
                    </td>

                    <td>
                        {{ $auditoria->admin?->name ?? '—' }}
                    </td>

                    <td>
                        {{ $auditoria->accion ?? '—' }}
                    </td>

                    <td>
                        {{ $auditoria->entidad ?? '—' }}
                    </td>

                    <td>
                        {{ $auditoria->entidad_id ?? '—' }}
                    </td>

                    <td>
                        {{ $auditoria->created_at?->format('d/m/Y H:i') ?? '—' }}
                    </td>

                    <td class="text-end">

                        <x-list.button-group>

                            @if ($canDetail)
                                <x-list.view-button :route="route('admin.auditoria.detail', ['audit_id' => $auditoria->id])" :target="false" />
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

    {{ $auditorias->links() }}

    <x-layout.loader.fullpage wire:loading.delay.short />

</div>

@script
    <script>
        Alpine.data('listAudit', () => ({
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

