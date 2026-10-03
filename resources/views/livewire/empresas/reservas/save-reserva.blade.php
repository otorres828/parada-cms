{{--
    NUEVA RESERVA
    --------------------------------------------------------------------------
    Crea una reserva propia con comprador sin cuenta y pasajeros históricos cifrados.
    La tasa es cero. El efectivo confirmado genera QR; la transferencia queda pendiente.

    Componentes utilizados:
    - <x-list.heading />: Encabezado y regreso.
    - <x-form.cancel-button />: Regreso al listado según permiso.
    - <x-layout.error />: Errores del servidor en español.
    - <x-empresas.reservas.tramo-taquilla />: Origen, destino y salida.
    - <x-empresas.reservas.pasajeros-cotizacion />: Pasajeros temporales.
    - <x-empresas.reservas.resumen-taquilla />: Desglose de la cotización.
    - <x-empresas.reservas.pagos-taquilla />: Pagos combinados sin persistir.
    - <x-money.dual />: Importes en ambas monedas.
    - <x-empresas.reservas.comprador />: Formulario de contacto del comprador.
    - <x-empresas.reservas.pasajero />: Datos del pasajero con validador Alpine.
    - <x-empresas.reservas.pasajes-taquilla />: Pasajes y sus QR cuando están pagados.
    - <x-layout.loader.fullpage />: Indicador de carga.
--}}
@section('title', 'Nueva Reserva')

<div class="py-3" x-data="taquilla">

    <x-list.heading>

        <x-slot:title>Nueva Reserva</x-slot:title>

        @if ($canList)
        
            <x-slot:button>

                <x-form.cancel-button :link="route('empresas.reservas.list')">
                    Volver al listado
                </x-form.cancel-button>

            </x-slot:button>

        @endif

    </x-list.heading>

    <x-layout.error />


    <x-empresas.reservas.tramo-taquilla 
        :origenes="$origenes" 
        :destinos="$destinos" 
        :opciones="$opciones" 
        :salidas="$salidas" 
    />

    <div class="row g-3 mb-3">

        <div class="col-lg-6">

            <x-empresas.reservas.comprador />
            
        </div>

        <div class="col-lg-6">

            <x-empresas.reservas.resumen-taquilla 
                :precio="$precio" 
                :cantidad="$cantidad" 
                :pasajeros="count($pasajeros)"
                :total="$total" 
                :abonado="$abonado" 
            />

        </div>

    </div>

    <div class="row">

        <div class="col-lg-6">

            <x-empresas.reservas.pasajero />

        </div>

        <div class="col-lg-6">

            <x-empresas.reservas.pasajeros-cotizacion 
                :pasajeros="$pasajeros" 
                :precio="$precio" 
            />

        </div>

    </div>

    <x-empresas.reservas.pagos-taquilla 
        :cuentas="$cuentas" 
        :pagos="$pagos" 
        :can-confirm="$canConfirm" 
        :cambio="$cambio" 
    />

    <form id="registrar-taquilla" x-data="formTaquilla('registrar')" x-ref="form" @submit.prevent="enviar" novalidate
        class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
        <strong>Total a pagar: ${{ number_format($total, 2) }} · Registrado en pantalla:
            ${{ number_format($abonado, 2) }}</strong>
        <button type="submit" class="btn btn-success" :disabled="saving"
            wire:loading.attr="disabled">Registrar</button>
    </form>

    <x-layout.loader.fullpage wire:loading.delay.short />

</div>

@script
    <script>
        Alpine.data('taquilla', () => ({
            init() {
                this.cleanup = Livewire.on('successEventList', data => this.$store.toast.success(data.message));
            },
            destroy() {
                this.cleanup?.();
            },
        }));
        Alpine.data('formTaquilla', accion => ({
            validator: null,
            saving: false,
            init() {
                this.$nextTick(() => {
                    this.validator = new JustValidate(this.$refs.form, {
                        errorLabelCssClass: ['invalid-feedback'],
                        errorFieldCssClass: ['is-invalid'],
                    });
                    Array.from(this.$refs.form.elements).filter(field => ['INPUT', 'SELECT'].includes(
                        field.tagName)).forEach((field, index) => {
                        field.id ||= `${accion}-campo-${index}`;
                        const rules = [];
                        if (field.required) rules.push({
                            rule: 'required',
                            errorMessage: 'Este campo es obligatorio.'
                        });
                        if (field.type === 'email') rules.push({
                            rule: 'email',
                            errorMessage: 'Ingresa un correo válido.'
                        });
                        if (rules.length) this.validator.addField(field, rules);
                    });
                });
            },
            async enviar() {
                if (this.saving || !this.validator || !await this.validator.revalidate()) return;
                this.saving = true;
                try {
                    await this.$wire.call(accion);
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
