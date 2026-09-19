{{--
    TERMINALES — DETALLE
    --------------------------------------------------------------------------
    Presenta el nombre, ubicación geográfica, dirección y estado de la terminal.

    Componentes reutilizables utilizados:
    - <x-list.heading />: Cabecera del módulo con título y acciones.
    - <x-form.cancel-button />: Enlace para regresar o cancelar la edición.
    - <x-list.status-badge />: Etiqueta visual del estado del registro.
    - <x-layout.loader.fullpage />: Indicador de carga durante las operaciones de Livewire.
    --------------------------------------------------------------------------
--}}

@section('title', 'Terminales')

<div x-data="detailTerminal" class="py-3">

    <x-list.heading>

        <x-slot:title>
            Terminales @if ($terminal_id)
                <small class="text-body-secondary">#{{ $terminal_id }}</small>

            @endif

        </x-slot:title>

        <x-slot:button>

            @if (\App\Services\Admin\Access::allows('terminales', 'list'))

                <x-form.cancel-button :link="route('admin.terminales.list')">
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
                            <dt class="col-sm-4">Terminal</dt>
                            <dd class="col-sm-8">
                                {{ $terminal->nombre ?? '—' }}
                            </dd>
                            <dt class="col-sm-4">Estado</dt>
                            <dd class="col-sm-8">
                                {{ $terminal->estado?->nombre ?? '—' }}
                            </dd>
                            <dt class="col-sm-4">Dirección</dt>
                            <dd class="col-sm-8">
                                {{ $terminal->direccion ?? '—' }}
                            </dd>
                            <dt class="col-sm-4">Estado</dt>
                            <dd class="col-sm-8">
                                <x-list.status-badge :status="$terminal->estatus" />
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
        Alpine.data('detailTerminal', () => ({
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
