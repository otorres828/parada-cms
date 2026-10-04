{{--
    PREGUNTAS FRECUENTES — FORMULARIO DEL CENTRO DE AYUDA
    --------------------------------------------------------------------------
    Permite crear o editar el contenido público de una pregunta frecuente, incluyendo
    categoría, URL, resumen, respuesta enriquecida, palabras clave, destacado y orden.

    Componentes reutilizables utilizados:
    - <x-list.heading />: Cabecera del formulario.
    - <x-form.cancel-button />: Regresa al listado.
    - <x-form.text-input />: Campos reutilizables de texto y orden.
    - <x-form.dropdown />: Selectores de categoría y estado.
    - <x-form.rich-text-editor />: Editor TinyMCE sincronizado con Livewire.
    - <x-layout.error />: Resumen de errores del servidor.
    - <x-layout.loader.fullpage />: Indicador global de carga.
    --------------------------------------------------------------------------
--}}

@section('title', 'Preguntas frecuentes')

<div x-data="savePregunta" class="py-3">

    <x-list.heading>

        <x-slot:title>
            {{ $pregunta_id ? 'Editar pregunta' : 'Nueva pregunta' }}
        </x-slot:title>

        <x-slot:button>

            <x-form.cancel-button :link="route('admin.preguntas-frecuentes.list')" >
                Volver al listado
            </x-form.cancel-button>

        </x-slot:button>

    </x-list.heading>

    <x-layout.error />

    @if ($categorias->isEmpty())

        <div class="alert alert-info" role="alert">

            Debe registrar una categoría activa antes de crear preguntas frecuentes.
            <a href="{{ route('admin.preguntas-frecuentes.categorias.add') }}" wire:navigate>
                Crear categoría
            </a>

        </div>

    @endif

    <form x-ref="form" @submit.prevent="preSave" novalidate>

        <div class="row g-3">

            <div class="col-md-6">

                <x-form.dropdown
                    label="Categoría"
                    name="categoria_pregunta_frecuente_id"
                    x-model="$wire.categoria_pregunta_frecuente_id"
                >
                    <option value="">Seleccione</option>
                    @foreach ($categorias as $categoria)
                        <option value="{{ $categoria->id }}">{{ $categoria->nombre }}</option>
                    @endforeach
                </x-form.dropdown>

            </div>

            <div class="col-md-6">

                <x-form.text-input name="pregunta" x-model="$wire.pregunta" >
                    Pregunta o título del artículo
                </x-form.text-input>

            </div>

            <div class="col-md-6">

                <x-form.text-input name="slug" x-model="$wire.slug" >
                    Slug público (opcional)
                </x-form.text-input>

                <div class="form-text">

                    Si lo deja vacío, se genera a partir de la pregunta.

                </div>

            </div>

            <div class="col-md-6">

                <x-form.text-input name="palabras_clave" x-model="$wire.palabras_clave" >
                    Palabras clave
                </x-form.text-input>

                <div class="form-text">

                    Separe los términos de búsqueda con comas.

                </div>

            </div>

            <div class="col-12">

                <label for="resumen" class="form-label">Resumen</label>
                <textarea id="resumen" name="resumen" class="form-control" rows="3" maxlength="500"
                    x-model="$wire.resumen"></textarea>

                <div class="form-text">

                    Este texto aparece en el listado público de la categoría.

                </div>

            </div>

            <div class="col-12">

                <label class="form-label" for="respuesta-{{ $pregunta_id ?? 'nuevo' }}">Respuesta</label>

                <x-form.rich-text-editor
                    id="respuesta-{{ $pregunta_id ?? 'nuevo' }}"
                    model="respuesta"
                />

            </div>

            <div class="col-md-4">

                <x-form.text-input type="number" name="orden" min="0" max="99999" x-model="$wire.orden" >
                    Orden de presentación
                </x-form.text-input>

            </div>

            <div class="col-md-4">

                <x-form.dropdown
                    label="Estado"
                    name="estatus"
                    x-model="$wire.estatus"
                >
                    <option value="1">Activo</option>
                    <option value="2">Inactivo</option>
                </x-form.dropdown>

            </div>

            <div class="col-md-4 d-flex align-items-end pb-2">

                <div class="form-check">

                    <input id="destacada" class="form-check-input" type="checkbox" x-model="$wire.destacada">
                    <label class="form-check-label" for="destacada">Mostrar en artículos populares</label>

                </div>

            </div>

        </div>

        <hr>

        <button class="btn btn-primary" type="submit" :disabled="saving || {{ $categorias->isEmpty() ? 'true' : 'false' }}"
            wire:loading.attr="disabled">
            Guardar
        </button>

    </form>

    <x-layout.loader.fullpage wire:loading.delay.short />

</div>

@script
    <script>
        Alpine.data('savePregunta', () => ({
            validator: null,
            saving: false,
            init() {
                this.$nextTick(() => {
                    this.validator = new JustValidate(this.$refs.form, {
                        errorLabelCssClass: ['invalid-feedback'],
                        errorFieldCssClass: ['is-invalid'],
                        successFieldCssClass: ['is-valid'],
                    });
                    this.validator.addField('[name="categoria_pregunta_frecuente_id"]', [{
                        rule: 'required',
                        errorMessage: 'Selecciona la categoría',
                    }]);
                    this.validator.addField('[name="pregunta"]', [{
                        rule: 'required',
                        errorMessage: 'Ingresa la pregunta',
                    }, {
                        rule: 'maxLength',
                        value: 255,
                        errorMessage: 'Máximo 255 caracteres',
                    }]);
                    this.validator.addField('[name="resumen"]', [{
                        rule: 'required',
                        errorMessage: 'Ingresa el resumen',
                    }, {
                        rule: 'maxLength',
                        value: 500,
                        errorMessage: 'Máximo 500 caracteres',
                    }]);
                    this.validator.addField('[name="orden"]', [{
                        rule: 'required',
                        errorMessage: 'Ingresa el orden',
                    }, {
                        rule: 'number',
                        errorMessage: 'El orden debe ser numérico',
                    }]);
                    this.validator.addField('[name="estatus"]', [{
                        rule: 'required',
                        errorMessage: 'Selecciona el estado',
                    }]);
                });
            },
            async preSave() {
                if (this.saving || ! this.validator || ! await this.validator.revalidate()) return;

                if (! this.$wire.respuesta || ! this.$wire.respuesta.replace(/<[^>]*>/g, '').trim()) {
                    this.$store.toast.info('Ingresa la respuesta.');
                    return;
                }

                this.saving = true;
                try {
                    await this.$wire.save();
                } finally {
                    this.saving = false;
                }
            },
            destroy() {
                this.validator?.destroy();
            },
        }));
    </script>
@endscript
