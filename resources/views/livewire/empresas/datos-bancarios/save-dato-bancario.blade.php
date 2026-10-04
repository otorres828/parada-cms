{{--
    GUARDAR DATOS BANCARIOS — EMPRESAS
    Alta y edición de cuentas propias. Valida en Alpine y en el servidor.
    Componentes: x-list.heading, x-form.cancel-button, x-layout.error, x-layout.loader.fullpage.
--}}

@section('title', 'Datos Bancarios')

<div class="py-3" x-data="saveDatoBancario">

    <x-list.heading>

        <x-slot:title>
            Datos Bancarios
        </x-slot:title>

        <x-slot:button>

            <x-form.cancel-button :link="route('empresas.datos-bancarios.list')" >
                Volver al listado
            </x-form.cancel-button>

        </x-slot:button>

    </x-list.heading>

    <x-layout.error />

    <form x-ref="form" @submit.prevent="enviar" novalidate class="card card-body">

        <div class="row g-3">

            <div class="col-md-6">

                <label class="form-label">Tipo</label>

                <select class="form-select" wire:model.live.number="datos.tipo">
                    <option value="1">Pago móvil</option>
                    <option value="2">Cuenta bancaria</option>
                </select>

            </div>

            <div class="col-md-6">

                <label class="form-label" for="banco-banco">Banco</label>

                <select id="banco-banco" class="form-select" wire:model="datos.banco" required>
                    <option value="">Seleccionar banco</option>
                    @foreach (config('bancos') as $banco)
                        <option value="{{ $banco }}">{{ $banco }}</option>
                    @endforeach
                </select>

            </div>

            <div class="col-md-6">

                <label class="form-label" for="banco-nombre_titular">Nombre del titular</label>

                <input id="banco-nombre_titular" class="form-control" wire:model="datos.nombre_titular" required>

            </div>

            <div class="col-md-6">

                <label class="form-label" for="banco-tipo_titular">Tipo de titular</label>

                <select id="banco-tipo_titular" class="form-select" wire:model="datos.tipo_titular" required>
                    <option value="personal">Personal</option>
                    <option value="juridico">Jurídico</option>
                    <option value="extranjero">Extranjero</option>
                </select>

            </div>

            <div class="col-md-6">

                <label class="form-label" for="banco-numero_documento">Documento del titular</label>

                <input id="banco-numero_documento" class="form-control" wire:model="datos.numero_documento" required>

            </div>

            <div class="col-md-6">

                <label class="form-label" for="banco-numero_cuenta_telefono">Cuenta (20 dígitos) o teléfono (11 dígitos)</label>

                <input id="banco-numero_cuenta_telefono" class="form-control" wire:model="datos.numero_cuenta_telefono" required>

            </div>

            <div class="col-md-6">

                <label class="form-label" for="banco-tipo_cuenta">Tipo de cuenta</label>

                <select id="banco-tipo_cuenta" class="form-select" wire:model="datos.tipo_cuenta">
                    <option value="">No aplica</option>
                    <option value="corriente">Corriente</option>
                    <option value="ahorro">Ahorro</option>
                </select>

            </div>

            <div class="col-md-6">

                <label class="form-label" for="banco-estatus">Estatus</label>

                <select id="banco-estatus" class="form-select" wire:model="datos.estatus" required>
                    <option value="1">Activo</option>
                    <option value="2">Inactivo</option>
                </select>

            </div>

        </div>

        <div class="mt-3">

            <button class="btn btn-primary" type="submit" :disabled="saving" wire:loading.attr="disabled">
                Guardar
            </button>

        </div>

    </form>

    <x-layout.loader.fullpage wire:loading.delay.short />

</div>

@script
    <script>
        Alpine.data('saveDatoBancario', () => ({
            validator: null,
            saving: false,
            init() {
                this.cleanup = Livewire.on('successEventList', data => this.$store.toast.success(data.message));
                this.$nextTick(() => {
                    this.validator = new JustValidate(this.$refs.form, {
                        errorLabelCssClass: ['invalid-feedback'],
                        errorFieldCssClass: ['is-invalid'],
                    });
                    this.$refs.form.querySelectorAll('[required]').forEach(field => {
                        this.validator.addField(field, [{
                            rule: 'required',
                            errorMessage: 'Este campo es obligatorio.',
                        }]);
                    });
                });
            },
            async enviar() {
                if (this.saving || !this.validator || !await this.validator.revalidate()) return;
                this.saving = true;
                try {
                    await this.$wire.save();
                } finally {
                    this.saving = false;
                }
            },
            destroy() {
                this.cleanup?.();
                this.validator?.destroy();
            },
        }));
    </script>
@endscript
