{{--
    POLÍTICAS DE EMBARQUE Y DESEMBARQUE — EMPRESAS
    --------------------------------------------------------------------------
    Edita las políticas de la empresa autenticada y permanece en el formulario al guardar.
    Componentes utilizados:
    - <x-list.heading />: Título de la sección.
    - <x-form.rich-text-editor />: Editor TinyMCE sincronizado con Livewire.
    - <x-layout.error />: Errores de validación.
    - <x-layout.loader.fullpage />: Indicador de carga.
--}}

@section('title', 'Políticas de embarque y desembarque')

<div class="py-3" x-data="savePoliticaEmbarque">

    <x-list.heading>

        <x-slot:title>
            Políticas de embarque y desembarque
        </x-slot:title>

    </x-list.heading>

    <x-layout.error />

    <div class="row">

        <div class="col-xl-8">

            <form wire:submit="save">

                <x-form.rich-text-editor
                    id="politicas-embarque-empresa"
                    model="contenido"
                />

                <div class="form-text mb-3">

                    Indica las condiciones de embarque y desembarque. Puedes utilizar títulos, listas, enlaces y tablas.

                </div>

                <hr>

                <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">Guardar</button>

            </form>

        </div>

    </div>

    <x-layout.loader.fullpage wire:loading.delay.short />

</div>

@script
    <script>
        Alpine.data('savePoliticaEmbarque', () => ({
            cleanup: null,

            init() {
                this.cleanup = Livewire.on('successEventList', data => this.$store.toast.success(data.message));
            },

            destroy() {
                this.cleanup?.();
            },
        }));
    </script>
@endscript
