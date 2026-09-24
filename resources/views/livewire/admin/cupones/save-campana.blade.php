{{--
    CAMPAÑAS — FORMULARIO
    --------------------------------------------------------------------------
    Permite definir o editar una campaña de cupones, sus condiciones de descuento, alcance,
    códigos y vigencia.

    Componentes reutilizables utilizados:
    - <x-list.heading />: Cabecera del módulo con título y acciones.
    - <x-form.cancel-button />: Enlace para regresar o cancelar la edición.
    - <x-layout.error />: Resumen de los errores de validación de Livewire.
    - <x-form.container-sm />: Contenedor de ancho limitado para los campos.
    - <x-form.dropdown />: Selector con etiqueta para las opciones del formulario.
    - <x-form.text-input />: Campo de entrada con etiqueta.
    - <x-layout.loader.fullpage />: Indicador de carga durante las operaciones de Livewire.
    --------------------------------------------------------------------------
--}}

@section('title', 'Campañas')

<div x-data="saveCampana" class="py-3">

    <x-list.heading>

        <x-slot:title>
            Campañas @if ($configuracion_cupon_id)
                <small class="text-body-secondary">#{{ $configuracion_cupon_id }}</small>
            @endif

        </x-slot:title>

        <x-slot:button>

            <x-form.cancel-button :link="route('admin.cupones.list')">
                Volver al listado
            </x-form.cancel-button>

        </x-slot:button>

    </x-list.heading>

    <x-layout.error />
    <form id="saveCampanaForm" x-ref="form" @submit.prevent="preSave" novalidate>

        <x-form.container-sm>

            <div class="mb-3">

                <x-form.dropdown label="Empresa (vacío para campaña general)" name="empresa_id"
                    x-model="$wire.empresa_id">

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

                <x-form.text-input type="text" name="nombre_campana" x-model="$wire.nombre_campana">
                    Nombre
                </x-form.text-input>

                @error('nombre_campana')
                    <div class="text-danger small">
                        {{ $message }}
                    </div>
                @enderror

            </div>

            <div class="mb-3">

                <x-form.dropdown label="Tipo de cupón" name="tipo_cupon" x-model="$wire.tipo_cupon">

                    <option value="">Seleccionar...</option>
                    <option value="1">Aleatorio</option>
                    <option value="2">Personalizado</option>

                </x-form.dropdown>

                @error('tipo_cupon')
                    <div class="text-danger small">
                        {{ $message }}
                    </div>
                @enderror

            </div>

            
            <div class="mb-3" x-show="Number($wire.tipo_cupon) === 2" x-cloak>

                <x-form.text-input type="text" name="codigo_personalizado" x-model="$wire.codigo_personalizado">
                    Código personalizado
                </x-form.text-input>

                @error('codigo_personalizado')
                    <div class="text-danger small">
                        {{ $message }}
                    </div>
                @enderror

            </div>
            
            <div class="mb-3">

                <x-form.dropdown label="Modalidad" name="modalidad" x-model="$wire.modalidad">

                    <option value="">Seleccionar...</option>
                    <option value="GENERAL">General</option>
                    <option value="PRIMERA_COMPRA">Primera compra</option>
                    <option value="USUARIO_NUEVO">Usuario nuevo</option>

                </x-form.dropdown>

                @error('modalidad')
                    <div class="text-danger small">
                        {{ $message }}
                    </div>
                @enderror

            </div>

            <div class="mb-3">

                <x-form.dropdown label="Aplicar descuento en" name="aplica_en" x-model="$wire.aplica_en">

                    <option value="">Seleccionar...</option>
                    <option value="reserva">Reserva general</option>
                    <option value="pasajes">Cada pasaje</option>

                </x-form.dropdown>

                @error('aplica_en')
                    <div class="text-danger small">
                        {{ $message }}
                    </div>
                @enderror

                <div class="form-text">
                    Reserva general aplica el valor una sola vez y lo distribuye entre los pasajes. Cada pasaje aplica
                    el valor completo individualmente a cada boleto.
                </div>

            </div>

            <div class="mb-3">

                <x-form.text-input type="number" name="cantidad_generar" x-model="$wire.cantidad_generar">
                    Cantidad de
                    cupones
                </x-form.text-input>

                @error('cantidad_generar')
                    <div class="text-danger small">
                        {{ $message }}
                    </div>
                @enderror

            </div>

            <div class="mb-3">

                <x-form.dropdown label="Descuento" name="tipo_descuento" x-model="$wire.tipo_descuento">

                    <option value="">Seleccionar...</option>
                    <option value="porcentaje">Porcentaje</option>
                    <option value="monto_fijo">Importe fijo USD</option>

                </x-form.dropdown>

                @error('tipo_descuento')
                    <div class="text-danger small">
                        {{ $message }}
                    </div>
                @enderror

            </div>

            <div class="mb-3">

                <x-form.text-input type="number" name="monto_descuento" x-model="$wire.monto_descuento" step="0.01">
                    Valor del
                    descuento
                </x-form.text-input>

                @error('monto_descuento')
                    <div class="text-danger small">
                        {{ $message }}
                    </div>
                @enderror

            </div>

            <div class="mb-3">

                <x-form.text-input type="datetime-local" name="fecha_inicio" x-model="$wire.fecha_inicio">
                    Inicio
                </x-form.text-input>

                @error('fecha_inicio')
                    <div class="text-danger small">
                        {{ $message }}
                    </div>
                @enderror

            </div>

            <div class="mb-3">

                <x-form.text-input type="datetime-local" name="fecha_fin" x-model="$wire.fecha_fin">
                    Fin
                </x-form.text-input>

                @error('fecha_fin')
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
        Alpine.data('saveCampana', () => ({
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
                        validator: () => true,
                        errorMessage: 'Revisa este campo'
                    }]);
                    this.validator.addField(this.$refs.form.querySelector('[name="nombre_campana"]'), [{
                        rule: 'required',
                        errorMessage: 'Este campo es requerido'
                    }, {
                        rule: 'maxLength',
                        value: 255,
                        errorMessage: 'Máximo 255 caracteres'
                    }]);
                    this.validator.addField(this.$refs.form.querySelector('[name="codigo_personalizado"]'), [{
                        validator: value => Number(this.$wire.tipo_cupon) !== 2 || value.trim().length > 0,
                        errorMessage: 'Este campo es requerido para cupones personalizados'
                    }, {
                        rule: 'maxLength',
                        value: 100,
                        errorMessage: 'Máximo 100 caracteres'
                    }]);
                    this.validator.addField(this.$refs.form.querySelector('[name="tipo_cupon"]'), [{
                        rule: 'required',
                        errorMessage: 'Este campo es requerido'
                    }]);
                    this.validator.addField(this.$refs.form.querySelector('[name="modalidad"]'), [{
                        rule: 'required',
                        errorMessage: 'Este campo es requerido'
                    }]);
                    this.validator.addField(this.$refs.form.querySelector('[name="aplica_en"]'), [{
                        rule: 'required',
                        errorMessage: 'Este campo es requerido'
                    }]);
                    this.validator.addField(this.$refs.form.querySelector('[name="cantidad_generar"]'),
                        [{
                            rule: 'required',
                            errorMessage: 'Este campo es requerido'
                        }]);
                    this.validator.addField(this.$refs.form.querySelector('[name="tipo_descuento"]'), [{
                        rule: 'required',
                        errorMessage: 'Este campo es requerido'
                    }]);
                    this.validator.addField(this.$refs.form.querySelector('[name="monto_descuento"]'),
                [{
                        rule: 'required',
                        errorMessage: 'Este campo es requerido'
                    }]);
                    this.validator.addField(this.$refs.form.querySelector('[name="fecha_inicio"]'), [{
                        rule: 'required',
                        errorMessage: 'Este campo es requerido'
                    }]);
                    this.validator.addField(this.$refs.form.querySelector('[name="fecha_fin"]'), [{
                        rule: 'required',
                        errorMessage: 'Este campo es requerido'
                    }]);
                    this.validator.addField(this.$refs.form.querySelector('[name="estatus"]'), [{
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
