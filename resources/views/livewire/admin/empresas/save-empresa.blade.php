{{--
    EMPRESAS — FORMULARIO
    --------------------------------------------------------------------------
    Permite crear o editar los datos de identificación y contacto de una empresa. Integra su
    datos generales y estado de la empresa en el mismo formulario.

    Componentes reutilizables utilizados:
    - <x-list.heading />: Cabecera del módulo con título y acciones.
    - <x-form.cancel-button />: Enlace para regresar o cancelar la edición.
    - <x-layout.error />: Resumen de los errores de validación de Livewire.
    - <x-form.container-sm />: Contenedor de ancho limitado para los campos.
    - <x-form.text-input />: Campo de entrada con etiqueta.
    - <x-form.dropdown />: Selector con etiqueta para las opciones del formulario.
    - <x-layout.loader.fullpage />: Indicador de carga durante las operaciones de Livewire.
    --------------------------------------------------------------------------
--}}

@section('title', 'Empresas')

<div x-data="saveEmpresa" class="py-3">

    <x-list.heading>

        <x-slot:title>
            Empresas @if ($empresa_id)
                <small class="text-body-secondary">#{{ $empresa_id }}</small>
            @endif

        </x-slot:title>

        <x-slot:button>

            <x-form.cancel-button :link="route('admin.empresas.list')">
                Volver al listado
            </x-form.cancel-button>

        </x-slot:button>

    </x-list.heading>

    <x-layout.error />
    <form id="saveEmpresaForm" x-ref="form" @submit.prevent="preSave" novalidate>

        <x-form.container-sm>

            <div class="mb-3">

                <x-form.text-input type="text" name="nombre" x-model="$wire.nombre">
                    Nombre
                </x-form.text-input>

                @error('nombre')
                    <div class="text-danger small">
                        {{ $message }}
                    </div>
                @enderror

            </div>

            <div x-show="Number($wire.tipo_contrato) === {{ \App\Models\Empresa::CONTRATO_ELLOS_RECIBEN }}" x-cloak>

                <div class="row g-3">

                    <div class="col-md-6">
                        <x-form.dropdown label="Día de corte" name="dia_corte" x-model="$wire.dia_corte">
                            <option value="">Seleccionar...</option>
                            <option value="1">Lunes</option>
                            <option value="2">Martes</option>
                            <option value="3">Miércoles</option>
                            <option value="4">Jueves</option>
                            <option value="5">Viernes</option>
                            <option value="6">Sábado</option>
                            <option value="7">Domingo</option>
                        </x-form.dropdown>
                        @error('dia_corte') <div class="text-danger small">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label" for="hora_corte">Hora de corte</label>
                        <input id="hora_corte" class="form-control" type="time" name="hora_corte" x-model="$wire.hora_corte">
                        @error('hora_corte') <div class="text-danger small">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6">
                        <x-form.dropdown label="Día de cierre" name="dia_vencimiento" x-model="$wire.dia_vencimiento">
                            <option value="">Seleccionar...</option>
                            <option value="1">Lunes</option>
                            <option value="2">Martes</option>
                            <option value="3">Miércoles</option>
                            <option value="4">Jueves</option>
                            <option value="5">Viernes</option>
                            <option value="6">Sábado</option>
                            <option value="7">Domingo</option>
                        </x-form.dropdown>
                        @error('dia_vencimiento') <div class="text-danger small">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label" for="hora_vencimiento">Hora de cierre</label>
                        <input id="hora_vencimiento" class="form-control" type="time" name="hora_vencimiento" x-model="$wire.hora_vencimiento">
                        @error('hora_vencimiento') <div class="text-danger small">{{ $message }}</div> @enderror
                    </div>

                </div>

                <p class="form-text mt-2">El corte genera la orden semanal y el cierre establece el límite para reportar el pago.</p>

            </div>

            <div class="mb-3">

                <x-form.text-input type="text" name="rif" x-model="$wire.rif">
                    Identificación fiscal
                </x-form.text-input>

                @error('rif')
                    <div class="text-danger small">
                        {{ $message }}
                    </div>
                @enderror

            </div>

            <div class="mb-3">

                <x-form.text-input type="text" name="telefono" x-model="$wire.telefono">
                    Teléfono
                </x-form.text-input>

                @error('telefono')
                    <div class="text-danger small">
                        {{ $message }}
                    </div>
                @enderror

            </div>

            <div class="mb-3">

                <x-form.text-input type="email" name="email" x-model="$wire.email">
                    Correo
                </x-form.text-input>

                @error('email')
                    <div class="text-danger small">
                        {{ $message }}
                    </div>
                @enderror

            </div>

            <div class="mb-3">

                <x-form.dropdown label="Gestión de pagos" name="tipo_contrato" x-model="$wire.tipo_contrato">

                    <option value="">Seleccionar...</option>
                    <option value="{{ \App\Models\Empresa::CONTRATO_ELLOS_RECIBEN }}">La empresa recibe los pagos</option>
                    <option value="{{ \App\Models\Empresa::CONTRATO_NOSOTROS_RECIBIMOS }}">La plataforma recibe los pagos</option>

                </x-form.dropdown>

                @error('tipo_contrato')
                    <div class="text-danger small">
                        {{ $message }}
                    </div>
                @enderror

            </div>

            <div class="mb-3">

                <x-form.dropdown label="Estado" name="estatus" x-model="$wire.estatus">

                    <option value="">Seleccionar...</option>
                    <option value="0">Inactivo</option>
                    <option value="1">Activo</option>

                </x-form.dropdown>

                @error('estatus')
                    <div class="text-danger small">
                        {{ $message }}
                    </div>
                @enderror

            </div>

        </x-form.container-sm>

        <hr>
        <button class="btn btn-primary" type="submit" :disabled="saving"
            wire:loading.attr="disabled">Guardar</button>
    </form>

    <x-layout.loader.fullpage wire:loading.delay.short />

</div>

@script
    <script>
        Alpine.data('saveEmpresa', () => ({
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
                    })
                    .addField(this.$refs.form.querySelector('[name="nombre"]'), [{
                        rule: 'required',
                        errorMessage: 'Este campo es requerido'
                    }, {
                        rule: 'maxLength',
                        value: 255,
                        errorMessage: 'Máximo 255 caracteres'
                    }])
                    .addField(this.$refs.form.querySelector('[name="rif"]'), [{
                        rule: 'required',
                        errorMessage: 'Este campo es requerido'
                    }, {
                        rule: 'maxLength',
                        value: 255,
                        errorMessage: 'Máximo 255 caracteres'
                    }])
                    .addField(this.$refs.form.querySelector('[name="telefono"]'), [{
                        rule: 'required',
                        errorMessage: 'Este campo es requerido'
                    }, {
                        rule: 'maxLength',
                        value: 255,
                        errorMessage: 'Máximo 255 caracteres'
                    }])
                    .addField(this.$refs.form.querySelector('[name="email"]'), [{
                        rule: 'required',
                        errorMessage: 'Este campo es requerido'
                    }, {
                        rule: 'email',
                        errorMessage: 'Ingresa un correo válido'
                    }, {
                        rule: 'maxLength',
                        value: 255,
                        errorMessage: 'Máximo 255 caracteres'
                    }])
                    .addField(this.$refs.form.querySelector('[name="tipo_contrato"]'), [{
                        rule: 'required',
                        errorMessage: 'Este campo es requerido'
                    }])
                    .addField(this.$refs.form.querySelector('[name="hora_corte"]'), [{
                        rule: 'required',
                        errorMessage: 'Este campo es requerido'
                    }])
                    .addField(this.$refs.form.querySelector('[name="hora_vencimiento"]'), [{
                        rule: 'required',
                        errorMessage: 'Este campo es requerido'
                    }])
                    .addField(this.$refs.form.querySelector('[name="estatus"]'), [{
                        rule: 'required',
                        errorMessage: 'Este campo es requerido'
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
