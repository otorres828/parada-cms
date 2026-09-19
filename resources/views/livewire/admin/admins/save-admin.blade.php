@section('title', $admin_id ? 'Editar administrador' : 'Nuevo administrador')

<div x-data="saveAdmin" class="py-3">

    <x-form.title>
        {{ $admin_id ? 'Editar administrador' : 'Nuevo administrador' }}
    </x-form.title>

    <x-layout.error />
    <form id="adminForm" x-ref="form" @submit.prevent="preSave" novalidate>

        <x-form.container-sm>

            <x-form.text-input icon="person-fill" name="name" x-model="$wire.name" maxlength="255">
                Nombre
            </x-form.text-input>

            <x-form.text-input icon="person-badge" name="username" x-model="$wire.username" maxlength="100">
                Usuario
            </x-form.text-input>

            <x-form.text-input icon="envelope" name="email" type="email" x-model="$wire.email" maxlength="255">
                Correo
            </x-form.text-input>

            <div x-data="{ show: false }">

                <x-form.text-input icon="lock-fill" name="password" x-bind:type="show ? 'text' : 'password'" x-model="$wire.password"
                    autocomplete="new-password">
                    Contraseña
                </x-form.text-input>

                <x-form.switch x-model="show">
                    Mostrar contraseña
                </x-form.switch>

            </div>

            @if ($admin_id)
                <p class="text-body-secondary mt-2">Deja la contraseña vacía para conservar la actual.</p>
            @endif

            <x-form.dropdown label="Estado" name="status" x-model="$wire.status">

                <option value="1">Activo</option>
                <option value="2">Inactivo</option>

            </x-form.dropdown>

        </x-form.container-sm>

        <hr>

        <x-form.subtitle>
            Permisos
        </x-form.subtitle>

        @if ($editingRoot)
            <p>Esta cuenta es root y conserva el acceso completo.</p>
        @else
            <x-form.switch x-model="$wire.is_superadmin">
                Superadmin
            </x-form.switch>

            <p class="text-body-secondary">El superadmin tiene acceso a todo el panel excepto a administradores.</p>

            <div x-show="!$wire.is_superadmin" class="row g-3">

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

                                                <input id="permission-{{ $permission->id }}" type="checkbox" class="form-check-input"
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

        <x-form.cancel-button :link="route('admin.admins.list')">
            Cancelar
        </x-form.cancel-button>

        <button class="btn btn-primary" type="submit" :disabled="saving" wire:loading.attr="disabled">Guardar administrador</button>
    </form>

    <x-layout.loader.fullpage wire:loading.delay.short />

</div>

@script
    <script>
        Alpine.data('saveAdmin', () => ({
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
                    this.validator
                        .addField('[name="name"]', [{
                            rule: 'required',
                            errorMessage: 'Ingresa el nombre'
                        }])
                        .addField('[name="username"]', [{
                            rule: 'required',
                            errorMessage: 'Ingresa el usuario'
                        }, {
                            rule: 'minLength',
                            value: 3,
                            errorMessage: 'Mínimo 3 caracteres'
                        }])
                        .addField('[name="email"]', [{
                            rule: 'required',
                            errorMessage: 'Ingresa el correo'
                        }, {
                            rule: 'email',
                            errorMessage: 'Correo inválido'
                        }])
                        .addField('[name="password"]', [{
                            validator: value => ($wire.admin_id && !value) || value.length >= 10,
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
                $wire.selectedPermissions = checked ? [...new Set([...selected, ...ids.map(String)])] : selected.filter(id => !ids
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
