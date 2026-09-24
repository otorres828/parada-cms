{{--
    SOLICITUDES DE EMPRESAS — DETALLE
    --------------------------------------------------------------------------
    Presenta los datos enviados por la agencia interesada y permite actualizar el estado
    de seguimiento de la solicitud cuando el administrador posee el permiso correspondiente.

    Componentes reutilizables utilizados:
    - <x-form.cancel-button />: Enlace para regresar al listado.
    - <x-layout.error />: Presenta errores de validación.
    - <x-layout.loader.fullpage />: Indicador global durante operaciones de Livewire.
    - <x-list.heading />: Cabecera del módulo.
    - <x-solicitudes.description />: Información de contacto y mensaje recibido.
    --------------------------------------------------------------------------
--}}

@section('title', 'Detalle de solicitud')

<div x-data="detailSolicitud" class="py-3">

    <x-list.heading>

        <x-slot:title>
            Solicitud <small class="text-body-secondary">#{{ $solicitud->id }}</small>
        </x-slot:title>

        <x-slot:button>
            <x-form.cancel-button :link="route('admin.solicitudes.list')">
                Volver al listado
            </x-form.cancel-button>
        </x-slot:button>

    </x-list.heading>

    <div class="container-fluid px-0">

        <div class="row g-3">

            <div class="col-lg-7">

                <div class="card h-100">
                    <x-solicitudes.description :$solicitud />
                </div>

            </div>

            <div class="col-lg-5">

                <div class="card">

                    <div class="card-body">

                        <h5 class="card-title mb-3">Seguimiento</h5>

                        <x-layout.error />

                        <label class="form-label" for="solicitud-estatus">Estado</label>
                        <select id="solicitud-estatus" class="form-select" wire:model="estatus" @disabled(! $canEdit)>
                            <option value="1">Nueva</option>
                            <option value="2">Contactada</option>
                            <option value="3">Cerrada</option>
                        </select>

                        @if ($canEdit)
                            <button type="button" class="btn btn-primary mt-3" wire:click="save">
                                Guardar estado
                            </button>
                        @endif

                    </div>

                </div>

            </div>

        </div>

    </div>

    <x-layout.loader.fullpage wire:loading.delay.short />

</div>

@script
    <script>
        Alpine.data('detailSolicitud', () => ({
            destroy() {
                this.toastCleanup?.forEach(cleanup => cleanup());
            },
            init() {
                this.toastCleanup = [
                    Livewire.on('successEventList', data => this.$store.toast.success(data.message)),
                ];
            },
        }));
    </script>
@endscript
