{{--
    PREGUNTAS FRECUENTES — FORMULARIO
    --------------------------------------------------------------------------
    Permite crear o editar una pregunta, su respuesta, orden de presentación y estado.

    Componentes reutilizables utilizados:
    - <x-list.heading />: Cabecera del formulario.
    - <x-form.cancel-button />: Regresa al listado.
    - <x-form.container-sm />: Limita el ancho del formulario.
    - <x-form.text-input />: Campo reutilizable para pregunta y orden.
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
            <x-form.cancel-button :link="route('admin.preguntas-frecuentes.list')">
                Volver al listado
            </x-form.cancel-button>
        </x-slot:button>

    </x-list.heading>

    <x-layout.error />

    <form x-ref="form" @submit.prevent="preSave" novalidate>

        <x-form.container-sm>

            <div class="mb-3">
                <x-form.text-input name="pregunta" x-model="$wire.pregunta">
                    Pregunta
                </x-form.text-input>
                @error('pregunta')
                    <div class="text-danger small">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="respuesta" class="form-label">Respuesta</label>
                <textarea id="respuesta" name="respuesta" class="form-control" rows="8" maxlength="15000"
                    x-model="$wire.respuesta"></textarea>
                @error('respuesta')
                    <div class="text-danger small">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <x-form.text-input type="number" name="orden" min="0" max="99999" x-model="$wire.orden">
                    Orden de presentación
                </x-form.text-input>
                @error('orden')
                    <div class="text-danger small">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <x-form.dropdown label="Estado" name="estatus" x-model="$wire.estatus">
                    <option value="1">Activo</option>
                    <option value="2">Inactivo</option>
                </x-form.dropdown>

                @error('estatus')
                    <div class="text-danger small">{{ $message }}</div>
                @enderror
            </div>

        </x-form.container-sm>

        <hr>

        <button class="btn btn-primary" type="submit" :disabled="saving" wire:loading.attr="disabled">
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
                    this.validator.addField('[name="pregunta"]', [{
                        rule: 'required',
                        errorMessage: 'Ingresa la pregunta',
                    }, {
                        rule: 'maxLength',
                        value: 255,
                        errorMessage: 'Máximo 255 caracteres',
                    }]);
                    this.validator.addField('[name="respuesta"]', [{
                        rule: 'required',
                        errorMessage: 'Ingresa la respuesta',
                    }, {
                        rule: 'maxLength',
                        value: 15000,
                        errorMessage: 'Máximo 15000 caracteres',
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
