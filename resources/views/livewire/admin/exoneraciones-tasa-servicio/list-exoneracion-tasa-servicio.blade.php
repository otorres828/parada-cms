{{--
    EXONERACIONES DE TASA DE SERVICIO — LISTADO
    --------------------------------------------------------------------------
    Consulta los períodos durante los cuales una empresa no cobra tasa de servicio. Permite
    filtrar por empresa y estado, editar, activar, inactivar o eliminar cada configuración.

    Componentes reutilizables utilizados:
    - <x-list.heading />: Cabecera del módulo.
    - <x-list.actions />: Contenedor del buscador, filtros y acción principal.
    - <x-list.search-input />: Buscador reactivo.
    - <x-list.add-button />: Enlace de creación.
    - <x-list.table />: Contenedor de la tabla.
    - <x-list.status-badge />: Estado visual del registro.
    - <x-list.button-group />: Agrupa las acciones del registro.
    - <x-list.status-button />: Activa o inactiva el registro.
    - <x-list.edit-button />: Enlace de edición.
    - <x-list.delete-button />: Elimina lógicamente el registro.
    - <x-layout.loader.fullpage />: Indicador global de carga.
    --------------------------------------------------------------------------
--}}

@section('title', 'Exoneraciones de tasa')

<div x-data="listExoneracionTasa" class="py-3">

    <x-list.heading>

        <x-slot:title>
            Exoneraciones de tasa de servicio
        </x-slot:title>

    </x-list.heading>

    <x-list.actions>

        <x-slot:search>
            <x-list.search-input wire:model.live.debounce.1200ms="search" />
        </x-slot:search>

        <x-slot:group>

            <select class="form-select" style="width: 240px;" wire:model.live="empresa_id"
                aria-label="Filtrar por empresa">
                <option value="">Todas las empresas</option>
                @foreach ($empresas as $empresa)
                    <option value="{{ $empresa->id }}">{{ $empresa->nombre }}</option>
                @endforeach
            </select>

            <select class="form-select" style="width: 200px;" wire:model.live="status"
                aria-label="Filtrar por estado">
                <option value="">Todos los estados</option>
                <option value="1">Activo</option>
                <option value="2">Inactivo</option>
            </select>

        </x-slot:group>

        @if ($canAdd)
            <x-slot:button>
                <x-list.add-button :route="route('admin.exoneraciones-tasa-servicio.add')">
                    Nueva exoneración
                </x-list.add-button>
            </x-slot:button>
        @endif

    </x-list.actions>

    <x-list.table>

        <thead>
            <tr>
                <th>Empresa</th>
                <th>Desde</th>
                <th>Hasta</th>
                <th>Motivo</th>
                <th>Estado</th>
                <th></th>
            </tr>
        </thead>

        <tbody>

            @forelse ($exoneraciones as $exoneracion)
                <tr wire:key="exoneracion-tasa-{{ $exoneracion->id }}">
                    <td>{{ $exoneracion->empresa?->nombre ?? '—' }}</td>
                    <td>{{ $exoneracion->fecha_desde->format('d/m/Y H:i') }}</td>
                    <td>{{ $exoneracion->fecha_hasta?->format('d/m/Y H:i') ?? 'Sin vencimiento' }}</td>
                    <td>{{ $exoneracion->motivo }}</td>
                    <td>
                        <x-list.status-badge :status="$exoneracion->estatus" />
                    </td>
                    <td class="text-end">

                        <x-list.button-group>

                            @if ($canEdit)
                                <x-list.status-button wire:click="changeStatus({{ $exoneracion->id }})"
                                    :status="$exoneracion->estatus" />
                                <x-list.edit-button :route="route('admin.exoneraciones-tasa-servicio.edit', $exoneracion->id)" />
                            @endif

                            @if ($canDelete)
                                <x-list.delete-button x-data
                                    @click="$dispatch('confirmDeletion', { id: {{ $exoneracion->id }} })" />
                            @endif

                        </x-list.button-group>

                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center py-4">No se encontraron exoneraciones.</td>
                </tr>
            @endforelse

        </tbody>

    </x-list.table>

    {{ $exoneraciones->links() }}

    <x-layout.loader.fullpage wire:loading.delay.short />

</div>

@script
    <script>
        Alpine.data('listExoneracionTasa', () => ({
            init() {
                this.toastCleanup = [
                    Livewire.on('successEventList', data => this.$store.toast.success(data.message)),
                    Livewire.on('errorEventList', data => this.$store.toast.info(data.message))
                ];
                window.addEventListener('confirmDeletion', this.confirmDeletion);
                const savedMessage = @js(session()->pull('admin_success'));
                if (savedMessage) this.$nextTick(() => this.$store.toast.success(savedMessage));
            },
            confirmDeletion: event => {
                Swal.fire({
                    title: '¿Estás seguro de eliminar esta exoneración?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar'
                }).then(result => {
                    if (result.isConfirmed) $wire.call('deleteExoneracion', event.detail.id);
                });
            },
            destroy() {
                window.removeEventListener('confirmDeletion', this.confirmDeletion);
                this.toastCleanup?.forEach(cleanup => cleanup());
            }
        }));
    </script>
@endscript
