{{-- Alta de pasajeros de taquilla; sus datos se guardan en el pasaje. --}}
@props(['habilitado' => false])
<div class="card mb-3" wire:key="formulario-pasajero">
    <div class="card-header">3. Pasajeros</div>
    <form class="card-body" x-data="pasajeroTaquilla({ tipo: $wire.entangle('pasajero.tipo_pasajero') })" @submit.prevent="enviar" x-ref="pasajeroForm" novalidate>
        @unless ($habilitado)
            <p class="text-muted small">Selecciona una salida para agregar pasajeros.</p>
        @endunless
        <fieldset class="border-0 p-0 m-0" @disabled(! $habilitado)>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label" for="taquilla-pasajero-nombre">Nombre</label>
                    <input id="taquilla-pasajero-nombre" type="text" class="form-control" wire:model="pasajero.nombre" required maxlength="255">
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="taquilla-pasajero-apellido">Apellido</label>
                    <input id="taquilla-pasajero-apellido" type="text" class="form-control" wire:model="pasajero.apellido" required maxlength="255">
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="taquilla-pasajero-fecha_nacimiento">Fecha de nacimiento</label>
                    <input id="taquilla-pasajero-fecha_nacimiento" type="date" max="{{ today()->toDateString() }}" class="form-control"
                        wire:model="pasajero.fecha_nacimiento" required maxlength="255">
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="taquilla-pasajero-tipo_pasajero">Tipo de pasajero</label>
                    <select id="taquilla-pasajero-tipo_pasajero" class="form-select" x-model="tipo">
                        <option value="adulto">Adulto</option>
                        <option value="nino">Niño (5-17)</option>
                        <option value="infante">Infante (0-4)</option>
                    </select>
                </div>
                <div class="col-md-6" x-show="tipo === 'infante'" x-cloak>
                    <label class="form-label" for="taquilla-pasajero-con_asiento">Asiento del infante</label>
                    <select id="taquilla-pasajero-con_asiento" class="form-select" wire:model.boolean="pasajero.con_asiento">
                        <option value="0">Sin asiento</option>
                        <option value="1">Con asiento</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="taquilla-pasajero-tipo_documento">Tipo de documento</label>
                    <select id="taquilla-pasajero-tipo_documento" class="form-select" wire:model="pasajero.tipo_documento">
                        <option value="">Sin documento</option>
                        <option value="1">Cédula</option>
                        <option value="2">DNI extranjero</option>
                        <option value="3">Pasaporte</option>
                        <option value="4">Otro</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="taquilla-pasajero-documento_identidad">Documento (opcional)</label>
                    <input id="taquilla-pasajero-documento_identidad" type="text" class="form-control" wire:model="pasajero.documento_identidad" maxlength="255">
                </div>
            </div>
            <button class="btn btn-primary mt-3" type="submit" wire:loading.attr="disabled" :disabled="saving">Agregar pasajero</button>
        </fieldset>
    </form>
</div>

@script
    <script>
        Alpine.data('pasajeroTaquilla', (estado) => {
            let validator = null;
            return {
                ...estado,
                saving: false,

                async enviar() {
                    if (this.saving) return;
                    const form = this.$refs.pasajeroForm;
                    validator?.destroy();
                    validator = new JustValidate(form, {
                        errorLabelCssClass: ['invalid-feedback'],
                        errorFieldCssClass: ['is-invalid'],
                    });
                    form.querySelectorAll('[required]').forEach(field => {
                        validator.addField(field, [{ rule: 'required', errorMessage: 'Este campo es obligatorio.' }]);
                    });
                    validator.isSubmitted = true;
                    this.saving = true;
                    try {
                        if (await validator.revalidate()) {
                            validator.destroy();
                            validator = null;
                            await this.$wire.agregarPasajero();
                        }
                    } finally {
                        this.saving = false;
                    }
                },
                destroy() {
                    validator?.destroy();
                },
            };
        });
    </script>
@endscript
