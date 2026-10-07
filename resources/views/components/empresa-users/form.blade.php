{{-- Formulario compartido de usuarios empresariales: credenciales, estado y permisos. --}}

@props(['groups', 'usuarioEmpresaId' => null, 'showAdminSwitch' => false, 'editingAdmin' => false])

<div x-data="saveEmpresaUsuarioForm">

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

            @if ($usuarioEmpresaId)
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

        @if ($showAdminSwitch)

            <x-form.switch name="es_admin" x-model="$wire.es_admin" >
                Administrador de empresa
            </x-form.switch>

            <p class="text-body-secondary" x-show="$wire.es_admin">El administrador tiene acceso a todo el panel de su empresa.</p>

        @elseif ($editingAdmin)
            <p>Esta cuenta ya es administradora y conserva el acceso completo.</p>
        @endif

        <div class="row g-3" x-show="{{ $showAdminSwitch ? '!$wire.es_admin' : ($editingAdmin ? 'false' : 'true') }}">

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

        <hr>

        <button class="btn btn-primary" type="submit" :disabled="saving" wire:loading.attr="disabled">Guardar
            usuario</button>
    </form>

</div>

@script
    <script>
        Alpine.data('saveEmpresaUsuarioForm', () => ({
            validator: null,
            saving: false,
            init() {
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
                                8,
                            errorMessage: 'La contraseña debe tener al menos 8 caracteres'
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
                this.validator?.destroy();
            },
        }));
    </script>
@endscript
