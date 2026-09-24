{{-- HOME — CONTACT --}}

@props(['solicitudEnviada'])

<section id="contacto" class="section contact-section">

    <div class="home-container contact-grid">

        <div class="contact-copy">
            <span class="section-kicker light">Para agencias</span>
            <h2>¿Representas una empresa de autobuses?</h2>
            <p>Conversemos sobre tu operación y cómo comenzar a vender de forma digital. Nuestro equipo te acompañará durante la incorporación.</p>

            <div class="contact-benefits">
                <div><i class="bi bi-check2"></i><span><strong>Configuración acompañada</strong><small>Organizamos contigo rutas, flota y equipo.</small></span></div>
                <div><i class="bi bi-check2"></i><span><strong>Accesos por responsabilidades</strong><small>Cada colaborador trabaja con sus permisos.</small></span></div>
                <div><i class="bi bi-check2"></i><span><strong>Operación lista para crecer</strong><small>Centraliza información y abre un nuevo canal de ventas.</small></span></div>
            </div>
        </div>

        <div class="contact-form-card">

            @if ($solicitudEnviada)

                <div class="form-success" role="status">
                    <span><i class="bi bi-check-lg"></i></span>
                    <h3>Recibimos tu solicitud</h3>
                    <p>Gracias por considerar Parada. Nuestro equipo revisará los datos y se pondrá en contacto contigo.</p>
                    <button type="button" wire:click="$set('solicitudEnviada', false)">Enviar otra solicitud</button>
                </div>

            @else

                <div class="form-heading">
                    <span>Hablemos de tu agencia</span>
                    <small>Completa los datos y te contactaremos.</small>
                </div>

                <x-layout.error />

                <form x-data="contactAgency" x-ref="form" @submit.prevent="preSave" novalidate>

                    <div class="honeypot" aria-hidden="true">
                        <label for="website">Sitio web</label>
                        <input id="website" name="website" type="text" wire:model="website" tabindex="-1" autocomplete="off">
                    </div>

                    <div class="form-grid">
                        <div class="field full-field">
                            <label for="nombre">Nombre y apellido</label>
                            <input id="nombre" name="nombre" type="text" wire:model="nombre" placeholder="¿Cómo podemos llamarte?" autocomplete="name">
                            @error('nombre') <small>{{ $message }}</small> @enderror
                        </div>
                        <div class="field">
                            <label for="empresa">Agencia o empresa</label>
                            <input id="empresa" name="empresa" type="text" wire:model="empresa" placeholder="Nombre comercial" autocomplete="organization">
                            @error('empresa') <small>{{ $message }}</small> @enderror
                        </div>
                        <div class="field">
                            <label for="cargo">Tu responsabilidad</label>
                            <input id="cargo" name="cargo" type="text" wire:model="cargo" placeholder="Dueño, apoderado, gerente…" autocomplete="organization-title">
                            @error('cargo') <small>{{ $message }}</small> @enderror
                        </div>
                        <div class="field">
                            <label for="telefono">Teléfono</label>
                            <input id="telefono" name="telefono" type="tel" wire:model="telefono" placeholder="+58 412 000 0000" autocomplete="tel">
                            @error('telefono') <small>{{ $message }}</small> @enderror
                        </div>
                        <div class="field">
                            <label for="email">Correo</label>
                            <input id="email" name="email" type="email" wire:model="email" placeholder="nombre@agencia.com" autocomplete="email">
                            @error('email') <small>{{ $message }}</small> @enderror
                        </div>
                        <div class="field full-field">
                            <label for="ciudad">Ciudad principal</label>
                            <input id="ciudad" name="ciudad" type="text" wire:model="ciudad" placeholder="Ciudad donde opera la agencia" autocomplete="address-level2">
                            @error('ciudad') <small>{{ $message }}</small> @enderror
                        </div>
                        <div class="field full-field">
                            <label for="mensaje">Cuéntanos sobre tu operación <span>Opcional</span></label>
                            <textarea id="mensaje" name="mensaje" wire:model="mensaje" rows="3" placeholder="Rutas principales, cantidad de unidades o cualquier dato que debamos conocer"></textarea>
                            @error('mensaje') <small>{{ $message }}</small> @enderror
                        </div>
                    </div>

                    <button class="button button-primary form-submit" type="submit" :disabled="saving"
                        wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="enviarSolicitud">Solicitar información <i class="bi bi-arrow-right"></i></span>
                        <span wire:loading wire:target="enviarSolicitud">Enviando solicitud…</span>
                    </button>

                    <p class="privacy-note"><i class="bi bi-lock"></i> Usaremos tus datos únicamente para atender esta solicitud.</p>

                </form>

            @endif

        </div>

    </div>

</section>

@script
<script>
    Alpine.data('contactAgency', () => ({
        validator: null,
        saving: false,
        init() {
            this.$nextTick(() => {
                this.validator = new JustValidate(this.$refs.form, {
                    errorLabelCssClass: ['invalid-feedback'],
                    errorFieldCssClass: ['is-invalid'],
                    successFieldCssClass: ['is-valid'],
                })
                .addField(this.$refs.form.querySelector('[name="nombre"]'), [{
                    rule: 'required',
                    errorMessage: 'Este campo es requerido'
                }, {
                    rule: 'maxLength',
                    value: 255,
                    errorMessage: 'Máximo 255 caracteres'
                }])
                .addField(this.$refs.form.querySelector('[name="empresa"]'), [{
                    rule: 'required',
                    errorMessage: 'Este campo es requerido'
                }, {
                    rule: 'maxLength',
                    value: 255,
                    errorMessage: 'Máximo 255 caracteres'
                }])
                .addField(this.$refs.form.querySelector('[name="cargo"]'), [{
                    rule: 'required',
                    errorMessage: 'Este campo es requerido'
                }, {
                    rule: 'maxLength',
                    value: 150,
                    errorMessage: 'Máximo 150 caracteres'
                }])
                .addField(this.$refs.form.querySelector('[name="telefono"]'), [{
                    rule: 'required',
                    errorMessage: 'Este campo es requerido'
                }, {
                    rule: 'maxLength',
                    value: 50,
                    errorMessage: 'Máximo 50 caracteres'
                }])
                .addField(this.$refs.form.querySelector('[name="email"]'), [{
                    rule: 'required',
                    errorMessage: 'Este campo es requerido'
                }, {
                    rule: 'email',
                    errorMessage: 'Ingresa un correo válido'
                }, {
                    rule: 'maxLength',
                    value: 255,
                    errorMessage: 'Máximo 255 caracteres'
                }])
                .addField(this.$refs.form.querySelector('[name="ciudad"]'), [{
                    rule: 'maxLength',
                    value: 150,
                    errorMessage: 'Máximo 150 caracteres'
                }])
                .addField(this.$refs.form.querySelector('[name="mensaje"]'), [{
                    rule: 'maxLength',
                    value: 1500,
                    errorMessage: 'Máximo 1500 caracteres'
                }]);
            });
        },
        async preSave() {
            if (this.saving || !this.validator || !await this.validator.revalidate()) return;

            this.saving = true;

            try {
                await $wire.call('enviarSolicitud');
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
