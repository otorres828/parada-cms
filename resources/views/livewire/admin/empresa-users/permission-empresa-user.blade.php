{{--
    USUARIOS DE EMPRESA
    --------------------------------------------------------------------------
    Presenta las secciones y permisos del personal de empresa para consultar y guardar sus
    autorizaciones.

    Componentes reutilizables utilizados:
    - <x-list.heading />: Cabecera del módulo con título y acciones.
    - <x-form.cancel-button />: Enlace para regresar o cancelar la edición.
    - <x-layout.error />: Resumen de los errores de validación de Livewire.
    - <x-layout.loader.fullpage />: Indicador de carga durante las operaciones de Livewire.
    --------------------------------------------------------------------------
--}}

@section('title', 'Usuarios de empresa')

<div x-data="permissionEmpresaUser" class="py-3">

    <x-list.heading>

        <x-slot:title>
            Usuarios de empresa @if ($usuario_empresa_id)
                <small class="text-body-secondary">#{{ $usuario_empresa_id }}</small>
            @endif

        </x-slot:title>

        <x-slot:button>

            @if ($canList)
                <x-form.cancel-button :link="route('admin.empresas.users.list', ['empresa_id' => $empresa_id])">
                    Volver al listado
                </x-form.cancel-button>
            @endif

        </x-slot:button>

    </x-list.heading>

    <x-layout.error />
    <form x-ref="form" @submit.prevent="preSave" novalidate>
        <p>Selecciona los permisos del colaborador dentro del panel de su empresa.</p>

        <div class="row g-3">

            @foreach ($permissions as $section => $items)
                <div class="col-md-4">

                    <div class="card h-100">

                        <div class="card-header">
                            {{ $section }}
                        </div>

                        <div class="card-body">

                            @foreach ($items as $permission)
                                <div class="form-check">

                                    <input id="permission-{{ $permission->id }}" class="form-check-input"
                                        type="checkbox"
                                        wire:model="selectedPermissions" value="{{ $permission->id }}">

                                    <label class="form-check-label" for="permission-{{ $permission->id }}">
                                        {{ $permission->name }}
                                    </label>

                                </div>
                            @endforeach

                        </div>

                    </div>

                </div>
            @endforeach

        </div>

        <button type="submit" class="btn btn-primary mt-3" :disabled="saving" wire:loading.attr="disabled">Guardar
            permisos</button>
    </form>
    <x-layout.loader.fullpage wire:loading.delay.short />

</div>

@script
    <script>
        Alpine.data('permissionEmpresaUser', () => ({
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
                });
            },
            async preSave() {
                if (this.saving || !this.validator || !await this.validator.revalidate()) return;
                this.saving = true;
                try {
                    await $wire.call('savePermissions');
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
