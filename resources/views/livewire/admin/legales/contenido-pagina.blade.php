{{--
    CONTENIDO LEGAL (FORMULARIO DE EDICIÓN)
    --------------------------------------------------------------------------
    Administra el contenido público de la página seleccionada y lo conserva en JSON.

    Componentes reutilizables utilizados:
    - <x-list.heading />: Cabecera de la página.
    - <x-layout.error />: Resumen de errores de validación.
    - <x-layout.loader.fullpage />: Indicador global de carga.
    --------------------------------------------------------------------------
--}}

@section('title', $titulo)

<div x-data="contenidoPagina">

    <x-list.heading>
        <x-slot:title>{{ $titulo }}</x-slot:title>
    </x-list.heading>

    <x-layout.error />

    <div class="row">

        <div class="col-xl-8">

            <form wire:submit="save" class="card card-body">

                <label for="contenido-pagina" class="form-label">
                    Contenido de la página
                </label>

                <textarea id="contenido-pagina" class="form-control mb-2" rows="20" wire:model="contenido"
                    required></textarea>

                <div class="form-text mb-3">
                    Escribe el texto con los párrafos que se mostrarán en el sitio.
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
