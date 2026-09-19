{{--
    EMPRESAS — LISTADO
    --------------------------------------------------------------------------
    Permite consultar las empresas de transporte. Incluye búsqueda, ordenación y paginación.
    Ofrece los filtros disponibles en la pantalla. Presenta las acciones de cada registro según
    las autorizaciones del administrador.

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
    - <x-list.edit-button />: Enlace para editar el registro.
    - <x-list.status-button />: Botón para solicitar un cambio de estado.
    - <x-layout.loader.fullpage />: Indicador de carga durante las operaciones de Livewire.
    --------------------------------------------------------------------------
--}}

@section('title', 'Empresas')

<div x-data="listEmpresa" class="py-3">

    <x-list.heading>

        <x-slot:title>
            Empresas
        </x-slot:title>

        <x-slot:button>

            @if (Route::has('admin.empresas.add') && $canAdd)
                <x-list.add-button :route="route('admin.empresas.add')">
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

            <label class="form-label" for="listEmpresa-status">
                Estado
            </label>

            <select id="listEmpresa-status" class="form-select" wire:model.live="status">

                <option value="">Todos</option>
                <option value="1">Activo</option>
                <option value="0">Inactivo</option>

            </select>

        </div>

    </div>

    <x-list.table>

        <thead>

            <tr>
                <th>ID
                    <x-list.sortable-button column="id" :$sortColumn :$sortDirection />
                </th>

                <th>Empresa
                    <x-list.sortable-button column="nombre" :$sortColumn :$sortDirection />
                </th>

                <th>Identificación
                    <x-list.sortable-button column="rif" :$sortColumn :$sortDirection />
                </th>

                <th>Correo
                    <x-list.sortable-button column="email" :$sortColumn :$sortDirection />
                </th>

                <th>Estado
                    <x-list.sortable-button column="estatus" :$sortColumn :$sortDirection />
                </th>

                <th class="text-end">Acciones</th>

            </tr>
        </thead>

        <tbody>

            @forelse ($empresas as $empresa)
                <tr wire:key="listEmpresa-{{ $empresa->id }}">
                    <td>
                        {{ $empresa->id }}
                    </td>

                    <td>
                        {{ $empresa->nombre ?? '—' }}
                    </td>

                    <td>
                        {{ $empresa->rif ?? '—' }}
                    </td>

                    <td>
                        {{ $empresa->email ?? '—' }}
                    </td>

                    <td>
                        <x-list.status-badge :status="$empresa->estatus" />
                    </td>

                    <td class="text-end">

                        <x-list.button-group>

                            @if ($canDetail)
                                <x-list.view-button :route="route('admin.empresas.detail', ['empresa_id' => $empresa->id])" :target="false" />
                            @endif

                            @if ($canEdit)
                                <x-list.edit-button :route="route('admin.empresas.edit', ['empresa_id' => $empresa->id])" />

                                <x-list.status-button
                                    wire:click="changeStatus({{ $empresa->id }})"
                                    :status="$empresa->estatus"
                                    wire:loading.attr="disabled" />
                            @endif

                        </x-list.button-group>

                    </td>

                </tr>

            @empty

                <tr>
                    <td colspan="6" class="text-center py-5">
                        No se encontraron registros.
                    </td>

                </tr>
            @endforelse

        </tbody>

    </x-list.table>

    {{ $empresas->links() }}

    <x-layout.loader.fullpage wire:loading.delay.short />

</div>

@script
    <script>
        Alpine.data('listEmpresa', () => ({

            savedMessage: @js(session()->pull('admin_success')),

            init() {

                Livewire.on('successEventList', data => {
                    this.$store.toast.success(data.message);
                });

                Livewire.on('errorEventList', data => {
                    this.$store.toast.info(data.message);
                });

                if (this.savedMessage) {

                    this.$nextTick(() => Livewire.dispatch('successEventList', {
                        message: this.savedMessage
                    }));

                }

            },

        }));
    </script>
@endscript
