@section('title', 'Auditoría')

<div x-data="listAudit" class="py-3">

    <x-list.heading>

        <x-slot:title>
            Auditoría
        </x-slot:title>

        <x-slot:button>

            @if (Route::has('admin.auditoria.add') && $canAdd)
                <x-list.add-button :route="route('admin.auditoria.add')">
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
                <th class="text-end">Acciones</th>
            </tr>
        </thead>

        <tbody>
            @forelse ($auditorias as $auditoria)

                <tr wire:key="listAudit-{{ $auditoria->id }}">
                    <td>{{ $auditoria->id }}</td>
                    <td>{{ $auditoria->admin?->name ?? '—' }}</td>
                    <td>{{ $auditoria->accion ?? '—' }}</td>
                    <td>{{ $auditoria->entidad ?? '—' }}</td>
                    <td>{{ $auditoria->entidad_id ?? '—' }}</td>
                    <td>{{ $auditoria->created_at?->format('d/m/Y H:i') ?? '—' }}</td>
                    <td class="text-end">

                        <x-list.button-group>

                            @if ($capabilities['detail'])
                                <x-list.view-button :route="route('admin.auditoria.detail', ['audit_id' => $auditoria->id])" :target="false" />
                            @endif

                        </x-list.button-group>

                    </td>
                </tr>
            @empty

                <tr>
                    <td colspan="7" class="text-center py-5">No se encontraron registros.</td>
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
