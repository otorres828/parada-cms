{{--
    CATEGORÍAS DEL CENTRO DE AYUDA — FORMULARIO
    --------------------------------------------------------------------------
    Permite crear o editar una categoría, su presentación pública, imagen, orden y estado.

    Componentes reutilizables utilizados:
    - <x-list.heading />: Cabecera del formulario.
    - <x-form.cancel-button />: Regresa al listado de categorías.
    - <x-form.container-md />: Contenedor de ancho limitado para el formulario.
    - <x-form.text-input />: Campos de texto y orden.
    - <x-form.dropdown />: Selector del estado.
    - <x-layout.error />: Resumen de errores del servidor.
    - <x-layout.loader.fullpage />: Indicador global de carga.
    --------------------------------------------------------------------------
--}}

@section('title', 'Categorías del centro de ayuda')

<div x-data="saveCategoriaPregunta" class="py-3">

    <x-list.heading>
        <x-slot:title>
            {{ $categoria_id ? 'Editar categoría' : 'Nueva categoría' }}
        </x-slot:title>

        <x-slot:button>
            <x-form.cancel-button :link="route('admin.preguntas-frecuentes.categorias.list')">
                Volver al listado
            </x-form.cancel-button>
        </x-slot:button>
    </x-list.heading>

    <x-layout.error />

    <form x-ref="form" @submit.prevent="preSave" novalidate>

        <x-form.container-md>

            <div class="row g-3">

                <div class="col-md-6">
                    <x-form.text-input name="nombre" x-model="$wire.nombre">
                        Nombre
                    </x-form.text-input>
                </div>

                <div class="col-md-6">
                    <x-form.text-input name="slug" x-model="$wire.slug">
                        Slug público (opcional)
                    </x-form.text-input>
                    <div class="form-text">Si lo deja vacío, se genera a partir del nombre.</div>
                </div>

                <div class="col-12">
                    <label for="descripcion" class="form-label">Descripción</label>
                    <textarea id="descripcion" name="descripcion" class="form-control" rows="3" maxlength="500"
                        x-model="$wire.descripcion"></textarea>
                </div>

                <div class="col-md-6">
                    <x-form.text-input name="icono" x-model="$wire.icono">
                        Icono de Bootstrap Icons
                    </x-form.text-input>
                    <div class="form-text">Ejemplo: bi-credit-card.</div>
                </div>

                <div class="col-md-6">
                    <label for="imagen" class="form-label">Imagen opcional</label>
                    <input id="imagen" name="imagen" type="file" class="form-control" wire:model="imagen"
                        accept="image/jpeg,image/png,image/webp">
                </div>

                @if ($imagen)
                    <div class="col-12">
                        <img src="{{ $imagen->temporaryUrl() }}" class="img-thumbnail" style="max-height: 220px;"
                            alt="Vista previa de la categoría">
                    </div>
                @elseif ($imagen_actual)
                    <div class="col-12">
                        <img src="{{ Storage::disk('public')->url($imagen_actual) }}" class="img-thumbnail"
                            style="max-height: 220px;" alt="Imagen actual de la categoría">
                    </div>
                @endif

                <div class="col-md-6">
                    <x-form.text-input type="number" name="orden" min="0" max="99999"
                        x-model="$wire.orden">
                        Orden de presentación
                    </x-form.text-input>
                </div>

                <div class="col-md-6">
                    <x-form.dropdown label="Estado" name="estatus" x-model="$wire.estatus">
                        <option value="1">Activo</option>
                        <option value="2">Inactivo</option>
                    </x-form.dropdown>
                </div>

                <div class="col-12">
                    <div class="form-check">
                        <input id="destacada" class="form-check-input" type="checkbox" x-model="$wire.destacada">
                        <label class="form-check-label" for="destacada">Mostrar en la portada del centro de
                            ayuda</label>
                    </div>
                </div>

            </div>

            <hr>

            <button class="btn btn-primary" type="submit" :disabled="saving" wire:loading.attr="disabled">
                Guardar
            </button>

        </x-form.container-md>

    </form>

    <x-layout.loader.fullpage wire:loading.delay.short />

</div>

@script
    <script>
        Alpine.data('saveCategoriaPregunta', () => ({
            validator: null,
            saving: false,
            init() {
                this.$nextTick(() => {
                    this.validator = new JustValidate(this.$refs.form, {
                        errorLabelCssClass: ['invalid-feedback'],
                        errorFieldCssClass: ['is-invalid'],
                        successFieldCssClass: ['is-valid'],
                    });
                    this.validator.addField('[name="nombre"]', [{
                        rule: 'required',
                        errorMessage: 'Ingresa el nombre de la categoría',
                    }, {
                        rule: 'maxLength',
                        value: 255,
                        errorMessage: 'Máximo 255 caracteres',
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
                if (this.saving || !this.validator || !await this.validator.revalidate()) return;
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
