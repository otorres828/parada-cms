{{--
    TRANSPORTES — ALTA
    --------------------------------------------------------------------------
    Registra un transporte propio y sus amenidades. El tipo se obtiene de la empresa.
    Componentes utilizados:
    - <x-list.heading /> y <x-form.cancel-button />: Encabezado y regreso.
    - <x-form.text-input />: Datos del transporte.
    - <x-form.container-sm /> y <x-form.dropdown />: Estatus.
    - <x-layout.error /> y <x-layout.loader.fullpage />: Errores y carga.
--}}

@section('title', $transporteId ? 'Editar transporte' : 'Nuevo transporte')

<div class="py-3" x-data="saveTransporte">

    <x-list.heading>

        <x-slot:title>
            {{ $transporteId ? 'Editar transporte #'.$transporteId : 'Nuevo transporte' }}
        </x-slot:title>

        <x-slot:button>

            <x-form.cancel-button :link="route('empresas.transportes.list')" >
                Volver al listado
            </x-form.cancel-button>

        </x-slot:button>

    </x-list.heading>

    <x-layout.error />

    <form x-ref="form" @submit.prevent="preSave" novalidate>

        <div class="row g-4">

            <div class="col-lg-6">

                <div class="card card-body">

                    <div class="row g-3">

                        <div class="col-md-6">

                            <x-form.text-input type="text" name="placa" wire:model="placa" maxlength="255" required >
                                Placa
                            </x-form.text-input>

                        </div>

                        <div class="col-md-6">

                            <x-form.text-input type="text" name="modelo" wire:model="modelo" maxlength="255" required >
                                Modelo
                            </x-form.text-input>

                        </div>

                        <div class="col-md-6">

                            <x-form.text-input type="text" name="tipo_asiento" wire:model="tipo_asiento" maxlength="255" required >
                                Tipo de asiento
                            </x-form.text-input>

                        </div>

                        <div class="col-md-6">

                            <x-form.text-input type="number" name="total_asientos" wire:model="total_asientos" min="1" max="100" required >
                                Cantidad de puestos
                            </x-form.text-input>

                        </div>

                    </div>

                </div>

            </div>

            <div class="col-lg-6">

                <div class="card">

                    <div class="card-header fw-semibold">

                        Amenidades del transporte

                    </div>

                    <div class="card-body">

                        <div class="row g-3">

                            @forelse ($amenidades as $amenidad)

                                <div class="col-sm-6" wire:key="transporte-amenidad-{{ $amenidad->id }}">

                                    <div class="form-check">

                                        <input id="amenidad-{{ $amenidad->id }}" class="form-check-input" type="checkbox"
                                            value="{{ $amenidad->id }}" wire:model="amenidadesSeleccionadas">
                                        <label class="form-check-label" for="amenidad-{{ $amenidad->id }}">
                                            {{ $amenidad->nombre }}
                                        </label>

                                    </div>

                                </div>

                            @empty

                                <div class="col-12 text-muted">

                                    No hay amenidades disponibles.

                                </div>

                            @endforelse

                        </div>

                    </div>

                </div>

            </div>

        </div>

        <div class="mt-4">

            <x-form.container-sm>

                <x-form.dropdown
                    label="Estatus"
                    name="estatus"
                    wire:model="estatus"
                >
                    <option value="1">Activo</option>
                    <option value="2">Inactivo</option>
                </x-form.dropdown>

            </x-form.container-sm>

        </div>

        <hr>

        <button class="btn btn-primary" type="submit" :disabled="saving" wire:loading.attr="disabled">Guardar</button>

    </form>

    <x-layout.loader.fullpage wire:loading.delay.short />

</div>

@script
    <script>
        Alpine.data('saveTransporte', () => ({
            saving: false,
            validator: null,
            init() {
                this.cleanup = Livewire.on('successEventList', data => this.$store.toast.success(data.message));
            },
            async preSave() {
                if (this.saving) return;
                this.validator?.destroy();
                this.validator = new JustValidate(this.$refs.form, {
                    errorLabelCssClass: ['invalid-feedback'],
                    errorFieldCssClass: ['is-invalid'],
                });
                this.$refs.form.querySelectorAll('[required]:not([disabled])').forEach(field => {
                    const rules = [{ rule: 'required', errorMessage: 'Este campo es obligatorio.' }];
                    if (field.type === 'number') {
                        rules.push({ validator: value => Number.isFinite(Number(value)) && Number(value) >= Number(field.min) && Number(value) <= Number(field.max), errorMessage: 'Ingresa un valor dentro del rango permitido.' });
                    }
                    this.validator.addField(field, rules);
                });
                this.validator.isSubmitted = true;
                if (!await this.validator.revalidate()) return;
                this.saving = true;
                try {
                    await this.$wire.save();
                } finally {
                    this.saving = false;
                }
            },
            destroy() {
                this.validator?.destroy();
                this.cleanup?.();
            },
        }));
    </script>
@endscript
