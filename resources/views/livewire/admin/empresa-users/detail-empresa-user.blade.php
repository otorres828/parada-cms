@section('title', 'Usuarios de empresa')

<div x-data="detailEmpresaUser" class="py-3">

    <x-list.heading>

        <x-slot:title>
            Usuarios de empresa @if ($usuario_empresa_id)
                <small class="text-body-secondary">#{{ $usuario_empresa_id }}</small>

            @endif

        </x-slot:title>

        <x-slot:button>

            @if (\App\Services\Admin\Access::allows('empresas.users', 'list'))

                <x-form.cancel-button :link="route('admin.empresas.users.list', ['empresa_id' => $empresa_id])">
                    Volver al listado
                </x-form.cancel-button>

            @endif

        </x-slot:button>

    </x-list.heading>

    <div class="container-fluid px-0 mb-4">

        <div class="row">

            <div class="col-md-6">

                <div class="card">

                    <div class="card-body">

                        <dl class="row mb-0">
                            <dt class="col-sm-4">Nombre</dt>
                            <dd class="col-sm-8">
                                {{ $usuarioEmpresa->nombre ?? '—' }}
                            </dd>
                            <dt class="col-sm-4">Correo</dt>
                            <dd class="col-sm-8">
                                {{ $usuarioEmpresa->email ?? '—' }}
                            </dd>
                            <dt class="col-sm-4">Administrador</dt>
                            <dd class="col-sm-8">
                                {{ $usuarioEmpresa->es_admin ? 'Sí' : 'No' }}
                            </dd>
                            <dt class="col-sm-4">Estado</dt>
                            <dd class="col-sm-8">
                                <x-list.status-badge :status="$usuarioEmpresa->estatus" />
                            </dd>
                        </dl>

                    </div>

                </div>

            </div>

        </div>

    </div>

    <x-layout.loader.fullpage wire:loading.delay.short />

</div>

@script
    <script>
        Alpine.data('detailEmpresaUser', () => ({
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
