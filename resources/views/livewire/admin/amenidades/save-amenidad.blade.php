{{--
    AMENIDADES — FORMULARIO
    --------------------------------------------------------------------------
    Permite crear o editar una amenidad, definiendo su nombre, icono y estado para el catálogo de
    comodidades de los autobuses.

    Componentes reutilizables utilizados:
    - <x-list.heading />: Cabecera del módulo con título y acciones.
    - <x-form.cancel-button />: Enlace para regresar o cancelar la edición.
    - <x-layout.error />: Resumen de los errores de validación de Livewire.
    - <x-form.container-sm />: Contenedor de ancho limitado para los campos.
    - <x-form.text-input />: Campo de entrada con etiqueta.
    - <x-form.dropdown />: Selector con etiqueta para opciones del formulario.
    - <x-layout.loader.fullpage />: Indicador de carga durante las operaciones de Livewire.
    --------------------------------------------------------------------------
--}}

@section('title', 'Amenidades')

<div x-data="saveAmenidad" class="py-3">

    <x-list.heading>

        <x-slot:title>
            Amenidades @if ($amenidad_id)
                <small class="text-body-secondary">#{{ $amenidad_id }}</small>

            @endif

        </x-slot:title>

        <x-slot:button>

            @if (\App\Services\Admin\Access::allows('amenidades', 'list'))

                <x-form.cancel-button :link="route('admin.amenidades.list')">
                    Volver al listado
                </x-form.cancel-button>

            @endif

        </x-slot:button>

    </x-list.heading>

    <x-layout.error />
    <form id="saveAmenidadForm" x-ref="form" @submit.prevent="preSave" novalidate>

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

            <div class="mb-3">

                <x-form.text-input type="text" name="icono" x-model="$wire.icono">
                    Ícono Bootstrap (ej. bi-wifi)
                </x-form.text-input>

                @error('icono')
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
        <button class="btn btn-primary" type="submit" :disabled="saving" wire:loading.attr="disabled">Guardar</button>
    </form>

    <x-layout.loader.fullpage wire:loading.delay.short />

</div>

@script
    <script>
        Alpine.data('saveAmenidad', () => ({
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
                    this.validator.addField(this.$refs.form.querySelector('[name="nombre"]'), [{
                        rule: 'required',
                        errorMessage: 'Este campo es requerido'
                    }, {
                        rule: 'maxLength',
                        value: 255,
                        errorMessage: 'Máximo 255 caracteres'
                    }]);
                    this.validator.addField(this.$refs.form.querySelector('[name="icono"]'), [{
                        rule: 'required',
                        errorMessage: 'Este campo es requerido'
                    }, {
                        rule: 'maxLength',
                        value: 64,
                        errorMessage: 'Máximo 64 caracteres'
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
