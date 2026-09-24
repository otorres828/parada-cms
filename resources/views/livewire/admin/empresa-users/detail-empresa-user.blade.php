{{--
    USUARIOS DE EMPRESA — DETALLE
    --------------------------------------------------------------------------
    Muestra la información de una cuenta de empresa, su condición de administrador y estado.

    Componentes reutilizables utilizados:
    - <x-list.heading />: Cabecera del módulo con título y acciones.
    - <x-form.cancel-button />: Enlace para regresar o cancelar la edición.
    - <x-list.status-badge />: Etiqueta visual del estado del registro.
    - <x-layout.loader.fullpage />: Indicador de carga durante las operaciones de Livewire.
    --------------------------------------------------------------------------
--}}

@section('title', 'Usuarios de empresa')

<div x-data="detailEmpresaUser" class="py-3">

    <x-list.heading>

        <x-slot:title>
            Usuarios de empresa @if ($usuario_empresa_id)
                <small class="text-body-secondary">#{{ $usuario_empresa_id }}</small>
            @endif

        </x-slot:title>

        <x-slot:button>

            <x-form.cancel-button :link="route('admin.empresas.users.list', ['empresa_id' => $empresa_id])">
                Volver al listado
            </x-form.cancel-button>

        </x-slot:button>

    </x-list.heading>

    <div class="container-fluid px-0 mb-4">

        <div class="row">

            <div class="col-md-6">

                <div class="card">

                    <x-empresa-users.description :usuario-empresa="$usuarioEmpresa" />

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

