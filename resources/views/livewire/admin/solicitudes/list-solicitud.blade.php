{{--
    SOLICITUDES DE EMPRESAS — LISTADO
    --------------------------------------------------------------------------
    Muestra las solicitudes enviadas por agencias interesadas desde el formulario público.
    Permite buscar por sus datos, filtrar por estado y abrir el detalle para su seguimiento.

    Componentes reutilizables utilizados:
    - <x-layout.loader.fullpage />: Indicador global durante operaciones de Livewire.
    - <x-list.actions />: Contenedor del buscador y los filtros.
    - <x-list.button-group />: Agrupa las acciones disponibles por registro.
    - <x-list.heading />: Cabecera del módulo.
    - <x-list.search-input />: Buscador reactivo del listado.
    - <x-list.sortable-button />: Control de ordenación por columna.
    - <x-list.table />: Contenedor reutilizable para tablas.
    - <x-list.view-button />: Enlace al detalle de la solicitud.
    --------------------------------------------------------------------------
--}}

@section('title', 'Solicitudes')

<div class="py-3">

    <x-list.heading>

        <x-slot:title>
            Solicitudes de empresas
        </x-slot:title>

    </x-list.heading>

    <x-list.actions>

        <x-slot:search>
            <x-list.search-input wire:model.live.debounce.1200ms="search" />
        </x-slot:search>

        <x-slot:group>

            <select class="form-select" wire:model.live="estatus" aria-label="Filtrar por estado">
                <option value="">Todos los estados</option>
                <option value="1">Nuevas</option>
                <option value="2">Contactadas</option>
                <option value="3">Cerradas</option>
            </select>

        </x-slot:group>

    </x-list.actions>

    <x-list.table>

        <thead>
            <tr>
                <th>
                    ID
                    <x-list.sortable-button column="id" :$sortColumn :$sortDirection />
                </th>
                <th>Empresa</th>
                <th>Representante</th>
                <th>Contacto</th>
                <th>Ciudad</th>
                <th>
                    Estado
                    <x-list.sortable-button column="estatus" :$sortColumn :$sortDirection />
                </th>
                <th>
                    Fecha
                    <x-list.sortable-button column="created_at" :$sortColumn :$sortDirection />
                </th>
                <th></th>
            </tr>
        </thead>

        <tbody>

            @forelse ($solicitudes as $solicitud)
                <tr class="align-middle" wire:key="solicitud-{{ $solicitud->id }}">
                    <td>{{ $solicitud->id }}</td>
                    <td>{{ $solicitud->empresa }}</td>
                    <td>
                        {{ $solicitud->nombre }}
                        <div class="small text-body-secondary">{{ $solicitud->cargo }}</div>
                    </td>
                    <td>
                        {{ $solicitud->telefono }}
                        <div class="small text-body-secondary">{{ $solicitud->email }}</div>
                    </td>
                    <td>{{ $solicitud->ciudad ?: '—' }}</td>
                    <td>
                        <span @class([
                            'badge',
                            'text-bg-primary' => $solicitud->estatus === 1,
                            'text-bg-warning' => $solicitud->estatus === 2,
                            'text-bg-secondary' => $solicitud->estatus === 3,
                        ])>
                            {{ $solicitud->estatus_nombre }}
                        </span>
                    </td>
                    <td>{{ $solicitud->created_at?->format('d/m/Y H:i') }}</td>
                    <td class="text-end">

                        <x-list.button-group>

                            @if ($canDetail)
                                <x-list.view-button :route="route('admin.solicitudes.detail', $solicitud->id)" :target="false" />
                            @endif

                        </x-list.button-group>

                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center py-5">
                        No se encontraron solicitudes.
                    </td>
                </tr>
            @endforelse

        </tbody>

    </x-list.table>

    {{ $solicitudes->links() }}

    <x-layout.loader.fullpage wire:loading.delay.short />

</div>
