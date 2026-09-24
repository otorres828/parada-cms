{{--
    AUDITORÍA — DETALLE
    --------------------------------------------------------------------------
    Presenta la acción administrativa registrada, su autor, entidad afectada, fecha y datos
    asociados para su revisión.

    Componentes reutilizables utilizados:
    - <x-auditoria.description />: Ficha descriptiva de la auditoría.
    - <x-form.cancel-button />: Enlace para regresar al listado anterior.
    - <x-layout.loader.fullpage />: Indicador global durante operaciones de Livewire.
    - <x-list.heading />: Cabecera del módulo con título y acciones.
    --------------------------------------------------------------------------
--}}

@section('title', 'Auditoría')

<div x-data="detailAudit" class="py-3">

    <x-list.heading>

        <x-slot:title>
            Auditoría @if ($audit_id)
                <small class="text-body-secondary">#{{ $audit_id }}</small>
            @endif

        </x-slot:title>

        <x-slot:button>

            <x-form.cancel-button :link="route('admin.auditoria.list')">
                Volver al listado
            </x-form.cancel-button>

        </x-slot:button>

    </x-list.heading>

    <div class="container-fluid px-0 mb-4">

        <div class="row">

            <div class="col-md-6">

                <div class="card">

                    <x-auditoria.description :auditoria="$auditoria" />

                </div>

            </div>

        </div>

    </div>

    <pre class="border rounded p-3">{{ json_encode($auditoria->datos, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>

    <x-layout.loader.fullpage wire:loading.delay.short />

</div>

@script
    <script>
        Alpine.data('detailAudit', () => ({
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


