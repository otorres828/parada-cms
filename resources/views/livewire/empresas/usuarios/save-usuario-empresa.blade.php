{{--
    USUARIOS DE EMPRESA — FORMULARIO
    --------------------------------------------------------------------------
    Permite al administrador de la empresa crear o editar usuarios, sus credenciales,
    estado y permisos por grupos y secciones. Las altas no conceden acceso de administrador.

    Componentes reutilizables utilizados:
    - <x-list.heading />: Encabezado con título y regreso al listado.
    - <x-layout.error />: Resumen de los errores de validación de Livewire.
    - <x-form.container-sm />: Contenedor de ancho limitado para los campos.
    - <x-form.text-input />: Campo de entrada con etiqueta.
    - <x-form.switch />: Interruptor para activar o desactivar una opción.
    - <x-form.dropdown />: Selector con etiqueta para las opciones del formulario.
    - <x-form.subtitle />: Subtítulo para organizar secciones del formulario.
    - <x-form.cancel-button />: Enlace para regresar o cancelar la edición.
    - <x-layout.loader.fullpage />: Indicador de carga durante las operaciones de Livewire.
    --------------------------------------------------------------------------
--}}

@section('title', $usuario_empresa_id ? 'Editar usuario' : 'Nuevo usuario')

<div x-data="saveUsuarioEmpresa" class="py-3">

    <x-list.heading>

        <x-slot:title>
            {{ $usuario_empresa_id ? 'Editar usuario' : 'Nuevo usuario' }}
        </x-slot:title>

        <x-slot:button>

            <x-form.cancel-button :link="route('empresas.usuarios.list')" >
                Volver al listado
            </x-form.cancel-button>

        </x-slot:button>

    </x-list.heading>

    <x-layout.error />

    <form id="usuarioEmpresaForm" x-ref="form" @submit.prevent="preSave" novalidate>

        <x-form.container-sm>

            <x-form.text-input icon="person-fill" name="nombre" x-model="$wire.nombre" maxlength="255" >
                Nombre
            </x-form.text-input>

            <x-form.text-input icon="envelope" name="email" type="email" x-model="$wire.email" maxlength="255" >
                Correo
            </x-form.text-input>

            <div x-data="{ show: false }">

                <x-form.text-input icon="lock-fill" name="password" x-bind:type="show ? 'text' : 'password'" x-model="$wire.password" autocomplete="new-password" >
                    Contraseña
                </x-form.text-input>

                <x-form.switch x-model="show" >
                    Mostrar contraseña
                </x-form.switch>

            </div>

            @if ($usuario_empresa_id)
                <p class="text-body-secondary mt-2">Deja la contraseña vacía para conservar la actual.</p>
            @endif

            <x-form.dropdown
                label="Estado"
                name="estatus"
                x-model="$wire.estatus"
            >

                <option value="1">Activo</option>
                <option value="2">Inactivo</option>

            </x-form.dropdown>

        </x-form.container-sm>

        <hr>

        <x-form.subtitle>
            Permisos
        </x-form.subtitle>

        @if ($editingAdmin)

            <p>Esta cuenta ya es administradora y conserva el acceso completo.</p>
        @else

            <div class="row g-3">

                @foreach ($groups as $group)
                    @if ($group->sections->isNotEmpty())

                        <div class="col-12">

                            <h3 class="h5 mt-3"><i class="bi {{ $group->icon }} me-2"></i>{{ $group->name }}</h3>

                        </div>

                        @foreach ($group->sections as $section)
                            @php($ids = $section->permissions->pluck('id')->map(fn($id) => (string) $id)->all())

                            <div class="col-md-6 col-xl-4" wire:key="section-{{ $section->id }}">

                                <div class="card h-100">

                                    <div class="card-header">

                                        <label class="form-check-label">

                                            <input type="checkbox" class="form-check-input me-2"
                                                :checked="sectionSelected(@js($ids))"
                                                @change="toggleSection(@js($ids), $event.target.checked)">
                                            {{ $section->name }} · Todos

                                        </label>

                                    </div>

                                    <div class="card-body">

                                        @foreach ($section->permissions as $permission)

                                            <div class="form-check" wire:key="permission-{{ $permission->id }}">

                                                <input id="permission-{{ $permission->id }}" type="checkbox"
                                                    class="form-check-input"
                                                    x-model="$wire.selectedPermissions" value="{{ $permission->id }}">

                                                <label for="permission-{{ $permission->id }}" class="form-check-label">
                                                    {{ $permission->name }}
                                                </label>

                                            </div>

                                        @endforeach

                                    </div>

                                </div>

                            </div>

                        @endforeach
                    @endif
                @endforeach

            </div>

        @endif

        <hr>

        <button class="btn btn-primary" type="submit" :disabled="saving" wire:loading.attr="disabled">Guardar
            usuario</button>
    </form>

    <x-layout.loader.fullpage wire:loading.delay.short />

</div>

@script
    <script>
        Alpine.data('saveUsuarioEmpresa', () => ({
            validator: null,
            saving: false,
            init() {
                this.toastCleanup = [
                    Livewire.on('empresas_usuario_success', data => this.$store.toast.success(data.message)),
                    Livewire.on('empresas_usuario_error', data => this.$store.toast.info(data.message)),
                ];
                this.$nextTick(() => {
                    this.validator = new JustValidate(this.$refs.form, {
                        errorLabelCssClass: ['invalid-feedback'],
                        errorFieldCssClass: ['is-invalid'],
                        successFieldCssClass: ['is-valid'],
                    });
                    this.validator
                        .addField('[name="nombre"]', [{
                            rule: 'required',
                            errorMessage: 'Ingresa el nombre'
                        }])
                        .addField('[name="email"]', [{
                            rule: 'required',
                            errorMessage: 'Ingresa el correo'
                        }, {
                            rule: 'email',
                            errorMessage: 'Correo inválido'
                        }])
                        .addField('[name="password"]', [{
                            validator: value => ($wire.usuario_empresa_id && !value) || value.length >=
                                10,
                            errorMessage: 'La contraseña debe tener al menos 10 caracteres'
                        }]);
                });
            },
            sectionSelected(ids) {
                const selected = $wire.selectedPermissions.map(String);
                return ids.length > 0 && ids.every(id => selected.includes(String(id)));
            },
            toggleSection(ids, checked) {
                const selected = $wire.selectedPermissions.map(String);
                $wire.selectedPermissions = checked ? [...new Set([...selected, ...ids.map(String)])] : selected
                    .filter(id => !ids
                        .map(String).includes(id));
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
