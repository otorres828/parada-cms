{{--
    EXONERACIONES DE TASA DE SERVICIO — FORMULARIO
    --------------------------------------------------------------------------
    Registra o modifica el período durante el cual las reservas de una empresa quedan exoneradas
    de tasa de servicio. Una fecha final vacía mantiene el beneficio sin vencimiento.

    Componentes reutilizables utilizados:
    - <x-list.heading />: Cabecera del formulario.
    - <x-form.container-sm />: Limita el ancho del formulario.
    - <x-form.dropdown />: Selectores de empresa y estado.
    - <x-form.text-input />: Entradas de fechas y motivo.
    - <x-form.cancel-button />: Regresa al listado.
    - <x-layout.error />: Presenta errores de validación.
    - <x-layout.loader.fullpage />: Indicador global de carga.
    --------------------------------------------------------------------------
--}}

@section('title', 'Exoneraciones de tasa')

<div x-data="saveExoneracionTasa" class="py-3">

    <x-list.heading>
        <x-slot:title>
            {{ $exoneracion_tasa_servicio_id ? 'Editar exoneración' : 'Nueva exoneración' }}
        </x-slot:title>
    </x-list.heading>

    <form x-ref="form" @submit.prevent="preSave" novalidate>

        <x-form.container-sm>

            <x-layout.error />

            <div class="mb-3">
                <x-form.dropdown label="Empresa" name="empresa_id" x-model="$wire.empresa_id">
                    <option value="">Selecciona una empresa</option>
                    @foreach ($empresas as $empresa)
                        <option value="{{ $empresa->id }}">{{ $empresa->nombre }}</option>
                    @endforeach
                </x-form.dropdown>
            </div>

            <div class="mb-3">
                <x-form.text-input type="datetime-local" name="fecha_desde" x-model="$wire.fecha_desde">
                    Fecha desde
                </x-form.text-input>
            </div>

            <div class="mb-3">
                <x-form.text-input type="datetime-local" name="fecha_hasta" x-model="$wire.fecha_hasta">
                    Fecha hasta
                </x-form.text-input>
                <small class="text-body-secondary">Déjala vacía para una exoneración sin vencimiento.</small>
            </div>

            <div class="mb-3">
                <x-form.text-input type="text" name="motivo" x-model="$wire.motivo">
                    Motivo
                </x-form.text-input>
            </div>

            <div class="mb-3">
                <x-form.dropdown label="Estado" name="estatus" x-model="$wire.estatus">
                    <option value="1">Activo</option>
                    <option value="2">Inactivo</option>
                </x-form.dropdown>
            </div>

            <x-form.cancel-button :link="route('admin.exoneraciones-tasa-servicio.list')">
                Cancelar
            </x-form.cancel-button>

            <button class="btn btn-primary" type="submit" :disabled="saving">Guardar</button>

        </x-form.container-sm>

    </form>

    <x-layout.loader.fullpage wire:loading.delay.short />

</div>

@script
    <script>
        Alpine.data('saveExoneracionTasa', () => ({
            validator: null,
            saving: false,
            init() {
                this.$nextTick(() => {
                    this.validator = new JustValidate(this.$refs.form, {
                        errorLabelCssClass: ['invalid-feedback'],
                        errorFieldCssClass: ['is-invalid']
                    });
                    this.validator.addField('[name="empresa_id"]', [{
                        rule: 'required',
                        errorMessage: 'Selecciona una empresa'
                    }]);
                    this.validator.addField('[name="fecha_desde"]', [{
                        rule: 'required',
                        errorMessage: 'Selecciona la fecha inicial'
                    }]);
                    this.validator.addField('[name="fecha_hasta"]', [{
                        validator: value => value === '' || value >= $wire.fecha_desde,
                        errorMessage: 'La fecha final debe ser posterior a la inicial'
                    }]);
                    this.validator.addField('[name="motivo"]', [{
                        rule: 'required',
                        errorMessage: 'Ingresa el motivo de la exoneración'
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
            }
        }));
    </script>
@endscript
