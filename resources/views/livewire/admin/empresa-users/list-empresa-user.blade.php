@section('title', 'Usuarios de empresa')

<div x-data="listEmpresaUser" class="py-3">

    <x-list.heading>

        <x-slot:title>
            Usuarios de empresa
        </x-slot:title>

        <x-slot:button>

            @if (Route::has('admin.empresas.users.add') && $canAdd)
                <x-list.add-button :route="route('admin.empresas.users.add', ['empresa_id' => $empresa_id])">
                    Nuevo registro
                </x-list.add-button>
            @endif

        </x-slot:button>

    </x-list.heading>

    @if ($errors->any())

        <div class="alert alert-danger" role="alert">

            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>

        </div>

    @endif

    <x-list.actions>

        <x-slot:search>

            <x-list.search-input wire:model.live.debounce.1200ms="search" />

        </x-slot:search>

        <x-slot:group>

        </x-slot:group>

    </x-list.actions>

    <div class="row g-3 mb-3">

        <div class="col-md-3">

            <label class="form-label" for="listEmpresaUser-status">
                Estado
            </label>

            <select id="listEmpresaUser-status" class="form-select" wire:model.live="status">

                <option value="">Todos</option>
                <option value="1">Activo</option>
                <option value="0">Inactivo</option>

            </select>

        </div>

        <div class="col-md-3">

            <label class="form-label" for="listEmpresaUser-from">
                Desde
            </label>
            <input id="listEmpresaUser-from" type="date" class="form-control" wire:model.live="date_from">
        </div>

        <div class="col-md-3">

            <label class="form-label" for="listEmpresaUser-to">
                Hasta
            </label>
            <input id="listEmpresaUser-to" type="date" class="form-control" wire:model.live="date_to">
        </div>

    </div>

    <x-list.table>

        <thead>

            <tr>
                <th>ID
                    <x-list.sortable-button column="id" :$sortColumn :$sortDirection />
                </th>
                <th>Nombre
                    <x-list.sortable-button column="nombre" :$sortColumn :$sortDirection />
                </th>
                <th>Correo
                    <x-list.sortable-button column="email" :$sortColumn :$sortDirection />
                </th>
                <th>Administrador
                    <x-list.sortable-button column="es_admin" :$sortColumn :$sortDirection />
                </th>
                <th>Estado
                    <x-list.sortable-button column="estatus" :$sortColumn :$sortDirection />
                </th>
                <th class="text-end">Acciones</th>
            </tr>
        </thead>

        <tbody>
            @forelse ($usuariosEmpresa as $usuarioEmpresa)

                <tr wire:key="listEmpresaUser-{{ $usuarioEmpresa->id }}">
                    <td>{{ $usuarioEmpresa->id }}</td>
                    <td>{{ $usuarioEmpresa->nombre ?? '—' }}</td>
                    <td>{{ $usuarioEmpresa->email ?? '—' }}</td>
                    <td>{{ $usuarioEmpresa->es_admin ? 'Sí' : 'No' }}</td>
                    <td>
                        <x-list.status-badge :status="$usuarioEmpresa->estatus" />
                    </td>
                    <td class="text-end">

                        <x-list.button-group>

                            @if ($capabilities['detail'])
                                <x-list.view-button :route="route('admin.empresas.users.detail', [
                                    'empresa_id' => $empresa_id,
                                    'usuario_empresa_id' => $usuarioEmpresa->id,
                                ])" :target="false" />
                            @endif
                            @if ($capabilities['edit'])
                                <x-list.edit-button :route="route('admin.empresas.users.edit', [
                                    'empresa_id' => $empresa_id,
                                    'usuario_empresa_id' => $usuarioEmpresa->id,
                                ])" />
                            @endif
                            @if ($capabilities['permissions'])
                                <a class="btn btn-outline-secondary"
                                    href="{{ route('admin.empresas.users.permissions', ['empresa_id' => $empresa_id, 'usuario_empresa_id' => $usuarioEmpresa->id]) }}"
                                    wire:navigate title="Permisos" aria-label="Permisos"><i class="bi bi-shield-lock-fill"></i></a>
                            @endif
                            @if ($canEdit)
                                <x-list.status-button wire:click="changeStatus({{ $usuarioEmpresa->id }})" :status="$usuarioEmpresa->estatus"
                                    wire:loading.attr="disabled" />
                            @endif

                        </x-list.button-group>

                    </td>
                </tr>
            @empty

                <tr>
                    <td colspan="6" class="text-center py-5">No se encontraron registros.</td>
                </tr>
            @endforelse
        </tbody>

    </x-list.table>

    {{ $usuariosEmpresa->links() }}

    <x-layout.loader.fullpage wire:loading.delay.short />

</div>

@script
    <script>
        Alpine.data('listEmpresaUser', () => ({
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
            async generateCoupons() {
                const result = await Swal.fire({
                    title: '¿Generar los cupones de esta campaña?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Generar',
                    cancelButtonText: 'Cancelar',
                });
                if (result.isConfirmed) await $wire.call('generateCoupons');
            },
        }));
    </script>
@endscript
