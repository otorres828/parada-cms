{{--
    USUARIOS DE EMPRESA — FORMULARIO
    --------------------------------------------------------------------------
    Permite crear o editar una cuenta del personal de la empresa, sus datos de acceso y estado.

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

@section('title', 'Usuarios de empresa')

<div x-data="saveEmpresaUser" class="py-3">

    <x-list.heading>

        <x-slot:title>
            Usuarios de empresa @if ($usuario_empresa_id)
                <small class="text-body-secondary">#{{ $usuario_empresa_id }}</small>

            @endif

        </x-slot:title>

        <x-slot:button>

            @if (\App\Services\Admin\Access::allows('empresas.users', 'list'))

                <x-form.cancel-button :link="route('admin.empresas.users.list', ['empresa_id' => $empresa_id])">
                    Volver al listado
                </x-form.cancel-button>

            @endif

        </x-slot:button>

    </x-list.heading>

    <x-layout.error />
    <form id="saveEmpresaUserForm" x-ref="form" @submit.prevent="preSave" novalidate>

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

                <x-form.text-input type="password" name="password" x-model="$wire.password" autocomplete="new-password">
                    Contraseña
                </x-form.text-input>

                @error('password')
                    <div class="text-danger small">
                        {{ $message }}
                    </div>
                @enderror

            </div>

            <div class="mb-3">

                <x-form.dropdown label="Administrador de empresa" name="es_admin" x-model="$wire.es_admin">

                    <option value="">Seleccionar...</option>
                    <option value="0">No</option>
                    <option value="1">Sí</option>

                </x-form.dropdown>

                @error('es_admin')
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

            @if ($usuario_empresa_id)

                <p class="text-body-secondary">Deja la contraseña vacía para conservar la actual.</p>

            @endif

        </x-form.container-sm>

        <hr>
        <button class="btn btn-primary" type="submit" :disabled="saving" wire:loading.attr="disabled">Guardar</button>
    </form>

    <x-layout.loader.fullpage wire:loading.delay.short />

</div>

@script
    <script>
        Alpine.data('saveEmpresaUser', () => ({
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
                    this.validator.addField(this.$refs.form.querySelector('[name="password"]'), [{
                        validator: value => Boolean($wire.usuario_empresa_id) || value.trim().length > 0,
                        errorMessage: 'Ingresa una contraseña'
                    }, {
                        rule: 'maxLength',
                        value: 255,
                        errorMessage: 'Máximo 255 caracteres'
                    }, {
                        rule: 'minLength',
                        value: 10,
                        errorMessage: 'Mínimo 10 caracteres'
                    }]);
                    this.validator.addField(this.$refs.form.querySelector('[name="es_admin"]'), [{
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
