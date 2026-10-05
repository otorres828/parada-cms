{{--
    MI PERFIL
    --------------------------------------------------------------------------
    Permite actualizar los datos del usuario de empresa autenticado mediante un formulario con
    validación y mensajes de resultado.

    Componentes reutilizables utilizados:
    - <x-form.title />: Título principal del formulario.
    - <x-layout.error />: Resumen de los errores de validación de Livewire.
    - <x-form.container-sm />: Contenedor de ancho limitado para los campos.
    - <x-form.text-input />: Campo de entrada con etiqueta.
    - <x-layout.loader.fullpage />: Indicador de carga durante las operaciones de Livewire.
    --------------------------------------------------------------------------
--}}

@section('title', 'Mi perfil')

<div x-data="profile" class="py-3">

    <x-form.title>
        Mi perfil
    </x-form.title>

    <form id="profileForm" x-ref="form" @submit.prevent="preSave" novalidate>

        <x-form.container-sm>

            <x-layout.error />

            <div class="mb-3">

                <x-form.text-input type="text" name="nombre" x-model="$wire.nombre" >
                    Nombre
                </x-form.text-input>

                @error('nombre')

                    <div class="text-danger small">

                        {{ $message }}

                    </div>

                @enderror

            </div>

            <div class="mb-3">

                <x-form.text-input type="email" name="email" x-model="$wire.email" >
                    Correo
                </x-form.text-input>

                @error('email')

                    <div class="text-danger small">

                        {{ $message }}

                    </div>

                @enderror

            </div>

            <div class="mb-3">

                <x-form.text-input type="password" name="current_password" x-model="$wire.current_password" autocomplete="current-password" >
                    Contraseña actual
                </x-form.text-input>

                @error('current_password')

                    <div class="text-danger small">

                        {{ $message }}

                    </div>

                @enderror

            </div>

        </x-form.container-sm>

        <hr><button class="btn btn-primary" type="submit" :disabled="saving" wire:loading.attr="disabled">Guardar
            cambios</button>
    </form>

    <x-layout.loader.fullpage wire:loading.delay.short />

</div>

@script
    <script>
        Alpine.data('profile', () => ({
            validator: null,
            saving: false,
            init() {
                this.toastCleanup = [
                    Livewire.on('empresas_profile_success', data => this.$store.toast.success(data.message)),
                    Livewire.on('empresas_profile_error', data => this.$store.toast.info(data.message)),
                ];
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
                    this.validator.addField(this.$refs.form.querySelector('[name="email"]'), [{
                        rule: 'required',
                        errorMessage: 'Este campo es requerido'
                    }, {
                        rule: 'email',
                        errorMessage: 'Ingresa un correo válido'
                    }, {
                        rule: 'maxLength',
                        value: 255,
                        errorMessage: 'Máximo 255 caracteres'
                    }]);
                    this.validator.addField(this.$refs.form.querySelector('[name="current_password"]'),
                        [{
                            rule: 'required',
                            errorMessage: 'Ingresa tu contraseña actual'
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
