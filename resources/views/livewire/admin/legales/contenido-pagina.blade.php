{{--
    CONTENIDO LEGAL (FORMULARIO DE EDICIÓN)
    --------------------------------------------------------------------------
    Administra el contenido público de la página seleccionada y lo conserva en JSON.

    Componentes reutilizables utilizados:
    - <x-list.heading />: Cabecera de la página.
    - <x-form.cancel-button />: Regresa al listado de documentos legales.
    - <x-form.rich-text-editor />: Editor TinyMCE sincronizado con Livewire.
    - <x-layout.error />: Resumen de errores de validación.
    - <x-layout.loader.fullpage />: Indicador global de carga.
    --------------------------------------------------------------------------
--}}

@section('title', $titulo)

<div x-data="contenidoPagina">

    <x-list.heading>

        <x-slot:title>
            {{ $titulo }}
        </x-slot:title>

        <x-slot:button>

            <x-form.cancel-button :link="route('admin.legales.documentos.list')">
                Volver
            </x-form.cancel-button>

        </x-slot:button>

    </x-list.heading>

    <x-layout.error />

    <div class="row">

        <div class="col-xl-8">

            <form wire:submit="save">

                <x-form.rich-text-editor id="contenido-pagina" model="contenido" />

                <div class="form-text mb-3">
                    Utiliza el editor para aplicar títulos, listas, enlaces, tablas y formato al contenido público.
                </div>

                <div>
                    <button class="btn btn-primary" wire:loading.attr="disabled">Guardar contenido</button>
                </div>

            </form>

        </div>

    </div>

    <x-layout.loader.fullpage wire:loading.delay.short />

</div>

@script

    <script>
        Alpine.data('contenidoPagina', () => ({

            init() {
                Livewire.on('successEventList', data => {
                    this.$store.toast.success(data.message);
                });
            },

        }));
    </script>

@endscript
