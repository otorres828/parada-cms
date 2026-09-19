{{--
    TASA DE SERVICIO — FORMULARIO
    --------------------------------------------------------------------------
    Permite definir o editar un rango de precios y su tasa de servicio, mediante monto fijo o
    porcentaje, con control del estado y validación de importes.

    Componentes reutilizables utilizados:
    - <x-list.heading />: Cabecera del módulo con título y acciones.
    - <x-form.container-sm />: Contenedor de ancho limitado para los campos.
    - <x-form.dropdown />: Selector con etiqueta para opciones del formulario.
    - <x-form.text-input />: Campo de entrada con etiqueta.
    - <x-form.cancel-button />: Enlace para regresar o cancelar la edición.
    - <x-layout.loader.fullpage />: Indicador de carga durante las operaciones de Livewire.
    --------------------------------------------------------------------------
--}}

@section('title', 'Tasa de Servicio')

<div x-data="saveTasaServicio" class="py-3">

    <x-list.heading>

        <x-slot:title>
            {{ $tasa_servicio_id ? 'Editar tasa de servicio' : 'Nueva tasa de servicio' }}
        </x-slot:title>

        <x-slot:button>

        </x-slot:button>

    </x-list.heading>

    <form x-ref="form" @submit.prevent="preSave" novalidate>

        <x-form.container-sm>

            <div class="mb-3">

                <x-form.dropdown label="Tipo de servicio" name="tipo_servicio" x-model="$wire.tipo_servicio">

                    <option value="1">Monto fijo</option>
                    <option value="2">Porcentaje</option>

                </x-form.dropdown>

                @error('tipo_servicio')
                    <div class="text-danger">
                        {{ $message }}
                    </div>
                @enderror

            </div>

            <div class="mb-3">

                <x-form.text-input type="number" name="monto_minimo" x-model="$wire.monto_minimo" min="0" step="0.01">
                    Monto mínimo USD
                </x-form.text-input>

                @error('monto_minimo')
                    <div class="text-danger">
                        {{ $message }}
                    </div>
                @enderror

            </div>

            <div class="mb-3">

                <x-form.text-input type="number" name="monto_maximo" x-model="$wire.monto_maximo" min="0" step="0.01">
                    Monto máximo USD
                </x-form.text-input>
                <small class="text-body-secondary">Déjalo vacío para un rango sin
                    límite superior. Ambos límites están incluidos.</small>
                @error('monto_maximo')
                    <div class="text-danger">
                        {{ $message }}
                    </div>
                @enderror

            </div>

            <div class="mb-3">

                <x-form.text-input type="number" name="cantidad" x-model="$wire.cantidad" min="0" step="0.01">
                    <span
                        x-text="Number($wire.tipo_servicio) === 2 ? 'Porcentaje sobre el precio final (%)' : 'Monto fijo por pasaje (USD)'"></span>
                </x-form.text-input>

                @error('cantidad')
                    <div class="text-danger">
                        {{ $message }}
                    </div>
                @enderror

            </div>

            <div class="mb-3">

                <x-form.dropdown label="Estado" name="estatus" x-model="$wire.estatus">

                    <option value="1">Activo</option>
                    <option value="0">Inactivo</option>

                </x-form.dropdown>

                @error('estatus')
                    <div class="text-danger">
                        {{ $message }}
                    </div>
                @enderror

            </div>

            <x-form.cancel-button :link="route('admin.tasas-servicio.list')">
                Cancelar
            </x-form.cancel-button>

            <button class="btn btn-primary" type="submit" :disabled="saving">Guardar</button>

        </x-form.container-sm>

    </form>

    <x-layout.loader.fullpage wire:loading.delay.short />

</div>

@script
    <script>
        Alpine.data('saveTasaServicio', () => ({
            validator: null,
            saving: false,
            init() {
                this.toastCleanup = [Livewire.on('successEventList', data => this.$store.toast.success(data.message)), Livewire.on(
                    'errorEventList', data => this.$store.toast.info(data.message))];
                this.$nextTick(() => {
                    this.validator = new JustValidate(this.$refs.form, {
                        errorLabelCssClass: ['invalid-feedback'],
                        errorFieldCssClass: ['is-invalid']
                    });
                    this.validator.addField('[name="monto_minimo"]', [{
                        rule: 'required',
                        errorMessage: 'Ingresa el monto mínimo'
                    }, {
                        rule: 'number',
                        errorMessage: 'Ingresa un número válido'
                    }]);
                    this.validator.addField('[name="cantidad"]', [{
                        rule: 'required',
                        errorMessage: 'Ingresa la tasa por pasaje'
                    }, {
                        rule: 'number',
                        errorMessage: 'Ingresa un número válido'
                    }, {
                        validator: value => Number(value) >= 0 && (Number($wire.tipo_servicio) !== 2 || Number(
                            value) <= 100),
                        errorMessage: 'El porcentaje debe estar entre 0 y 100'
                    }]);
                    this.validator.addField('[name="monto_maximo"]', [{
                        validator: value => value === '' || Number(value) >= Number($wire.monto_minimo),
                        errorMessage: 'El máximo debe ser mayor o igual al mínimo'
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
                this.validator?.destroy();
                this.toastCleanup?.forEach(cleanup => cleanup());
            }
        }));
    </script>
@endscript
