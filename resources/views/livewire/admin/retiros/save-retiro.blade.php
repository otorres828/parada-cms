{{--
    RETIROS — FORMULARIO
    --------------------------------------------------------------------------
    Permite registrar una solicitud de retiro de una empresa, indicando monto, datos bancarios y
    observaciones.

    Componentes reutilizables utilizados:
    - <x-list.heading />: Cabecera del módulo con título y acciones.
    - <x-form.cancel-button />: Enlace para regresar o cancelar la edición.
    - <x-layout.error />: Resumen de los errores de validación de Livewire.
    - <x-form.container-sm />: Contenedor de ancho limitado para los campos.
    - <x-form.dropdown />: Selector con etiqueta para opciones del formulario.
    - <x-form.text-input />: Campo de entrada con etiqueta.
    - <x-form.textarea />: Campo de texto de varias líneas.
    - <x-layout.loader.fullpage />: Indicador de carga durante las operaciones de Livewire.
    --------------------------------------------------------------------------
--}}

@section('title', 'Retiros')

<div x-data="saveRetiro" class="py-3">

    <x-list.heading>

        <x-slot:title>
            Retiros @if ($retiro_id)
                <small class="text-body-secondary">#{{ $retiro_id }}</small>

            @endif

        </x-slot:title>

        <x-slot:button>

            @if (\App\Services\Admin\Access::allows('retiros', 'list'))

                <x-form.cancel-button :link="route('admin.retiros.list')">
                    Volver al listado
                </x-form.cancel-button>

            @endif

        </x-slot:button>

    </x-list.heading>

    <x-layout.error />
    <form id="saveRetiroForm" x-ref="form" @submit.prevent="preSave" novalidate>

        <x-form.container-sm>

            <div class="mb-3">

                <input type="search" class="form-control mb-2" placeholder="Buscar opciones..." aria-label="Buscar Empresa"
                    wire:model.live.debounce.500ms="search_empresa_id">

                <x-form.dropdown label="Empresa" name="empresa_id" x-model="$wire.empresa_id">

                    <option value="">Seleccionar...</option>

                    @foreach ($options_empresa_id as $value => $label)

                        <option value="{{ $value }}">{{ $label }}</option>

                    @endforeach

                </x-form.dropdown>

                @error('empresa_id')
                    <div class="text-danger small">
                        {{ $message }}
                    </div>
                @enderror

            </div>

            <div class="mb-3">

                <x-form.text-input type="number" name="monto" x-model="$wire.monto" step="0.01">
                    Monto USD
                </x-form.text-input>

                @error('monto')
                    <div class="text-danger small">
                        {{ $message }}
                    </div>
                @enderror

            </div>

            <div class="mb-3">

                <x-form.textarea name="datos_bancarios" x-model="$wire.datos_bancarios" rows="3">
                    Datos bancarios del
                    beneficiario
                </x-form.textarea>

                @error('datos_bancarios')
                    <div class="text-danger small">
                        {{ $message }}
                    </div>
                @enderror

            </div>

            <div class="mb-3">

                <x-form.textarea name="comentario" x-model="$wire.comentario" rows="3">
                    Observaciones
                </x-form.textarea>

                @error('comentario')
                    <div class="text-danger small">
                        {{ $message }}
                    </div>
                @enderror

            </div>

        </x-form.container-sm>

        <hr>
        <button class="btn btn-primary" type="submit" :disabled="saving" wire:loading.attr="disabled">Guardar</button>
    </form>

    <x-layout.loader.fullpage wire:loading.delay.short />

</div>

@script
    <script>
        Alpine.data('saveRetiro', () => ({
            validator: null,
            saving: false,
            init() {
                this.toastCleanup = [
                    Livewire.on('successEventList', data => this.$store.toast.success(data.message)),
                    Livewire.on('errorEventList', data => this.$store.toast.info(data.message)),
                ];
                const savedMessage = @js(session()->pull('admin_success'));
                if (savedMessage) this.$nextTick(() => Livewire.dispatch('successEventList', {
                    message: savedMessage
                }));
                this.$nextTick(() => {
                    this.validator = new JustValidate(this.$refs.form, {
                        errorLabelCssClass: ['invalid-feedback'],
                        errorFieldCssClass: ['is-invalid'],
                        successFieldCssClass: ['is-valid'],
                    });
                    this.validator.addField(this.$refs.form.querySelector('[name="empresa_id"]'), [{
                        rule: 'required',
                        errorMessage: 'Este campo es requerido'
                    }]);
                    this.validator.addField(this.$refs.form.querySelector('[name="monto"]'), [{
                        rule: 'required',
                        errorMessage: 'Este campo es requerido'
                    }]);
                    this.validator.addField(this.$refs.form.querySelector('[name="datos_bancarios"]'), [{
                        rule: 'required',
                        errorMessage: 'Este campo es requerido'
                    }, {
                        rule: 'maxLength',
                        value: 2000,
                        errorMessage: 'Máximo 2000 caracteres'
                    }]);
                    this.validator.addField(this.$refs.form.querySelector('[name="comentario"]'), [{
                        rule: 'maxLength',
                        value: 2000,
                        errorMessage: 'Máximo 2000 caracteres'
                    }]);
                });
            },
            async preSave() {
                if (this.saving || !this.validator || !await this.validator.revalidate()) return;
                this.saving = true;
                try {
                    await $wire.call('save');
                } finally {
                    this.saving = false;
                }
            },
            destroy() {
                this.toastCleanup?.forEach(cleanup => cleanup());
                this.validator?.destroy();
            },
        }));
    </script>
@endscript
