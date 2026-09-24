{{--
    EMPRESAS — DETALLE
    --------------------------------------------------------------------------
    Presenta la identificación, correo y estado de la empresa seleccionada.

    Componentes reutilizables utilizados:
    - <x-list.heading />: Cabecera del módulo con título y acciones.
    - <x-form.cancel-button />: Enlace para regresar o cancelar la edición.
    - <x-list.status-badge />: Etiqueta visual del estado del registro.
    - <x-layout.loader.fullpage />: Indicador de carga durante las operaciones de Livewire.
    --------------------------------------------------------------------------
--}}

@section('title', 'Empresas')

<div x-data="detailEmpresa" class="py-3">

    <x-list.heading>

        <x-slot:title>
            Empresas @if ($empresa_id)
                <small class="text-body-secondary">#{{ $empresa_id }}</small>
            @endif

        </x-slot:title>

        <x-slot:button>

            <x-form.cancel-button :link="route('admin.empresas.list')">
                Volver al listado
            </x-form.cancel-button>

        </x-slot:button>

    </x-list.heading>

    <div class="container-fluid px-0 mb-4">

        <div class="row">

            <div class="col-md-6">

                <div class="card">

                    <div class="card-body">

                        <dl class="row mb-0">
                            <dt class="col-sm-4">Empresa</dt>
                            <dd class="col-sm-8">
                                {{ $empresa->nombre ?? '—' }}
                            </dd>
                            <dt class="col-sm-4">Identificación</dt>
                            <dd class="col-sm-8">
                                {{ $empresa->rif ?? '—' }}
                            </dd>
                            <dt class="col-sm-4">Correo</dt>
                            <dd class="col-sm-8">
                                {{ $empresa->email ?? '—' }}
                            </dd>
                            <dt class="col-sm-4">Estado</dt>
                            <dd class="col-sm-8">
                                <x-list.status-badge :status="$empresa->estatus" />
                            </dd>
                        </dl>

                    </div>

                </div>

                <div class="alert alert-light border">
                    Saldo contable: {{ $balance['saldo'] }} · Reservado: {{ $balance['retenido'] }} ·
                    Disponible: {{ $balance['disponible'] }}
                </div>

                @if ($canListUser)
                    <a class="btn btn-primary"
                        href="{{ route('admin.empresas.users.list', ['empresa_id' => $empresa->id]) }}"
                        wire:navigate>Gestionar
                        usuarios de la empresa</a>
                @endif

            </div>

        </div>

    </div>

    <x-layout.loader.fullpage wire:loading.delay.short />

</div>

@script
    <script>
        Alpine.data('detailEmpresa', () => ({
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

