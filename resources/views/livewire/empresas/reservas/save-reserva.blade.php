{{--
    NUEVA RESERVA
    --------------------------------------------------------------------------
    Cotiza una venta de taquilla por etapas. Solo persiste al pulsar Registrar.
    Componentes utilizados:
    - <x-list.heading /> y <x-form.cancel-button />: Título y regreso.
    - <x-layout.error />: Errores de validación del servidor.
    - <x-empresas.reservas.tramo-taquilla />: Selección del viaje.
    - <x-empresas.reservas.comprador />: Contacto del comprador.
    - <x-empresas.reservas.pasajero />: Alta temporal con Alpine independiente.
    - <x-empresas.reservas.pasajeros-cotizacion />: Pasajeros de esta venta.
    - <x-empresas.reservas.pagos-taquilla />: Pagos y conversión de monedas.
    - <x-empresas.reservas.transporte-taquilla />: Transporte y amenidades.
    - <x-empresas.reservas.resumen-taquilla />: Totales de la venta.
    - <x-empresas.reservas.confirmacion-taquilla />: Resumen previo a confirmar el cobro.
    - <x-layout.loader.fullpage />: Indicador de carga.
--}}
@section('title', 'Nueva Reserva')

<div class="py-3" x-data="saveReserva" @cotizacion-actualizada.window="resaltarCambios($event.detail.secciones)">

    <x-list.heading>
        <x-slot:title>Nueva Reserva</x-slot:title>
        @if ($canList)
            <x-slot:button>
                <x-form.cancel-button :link="route('empresas.reservas.list')">Volver al listado</x-form.cancel-button>
            </x-slot:button>
        @endif
    </x-list.heading>

    <p class="text-muted mb-3">Selecciona el viaje, agrega a los pasajeros y registra los pagos para completar la venta.</p>
    <x-layout.error />

    <x-empresas.reservas.tramo-taquilla :origenes="$origenes" :destinos="$destinos" :opciones="$opciones" :salidas="$salidas" />

    <div class="row g-4">
        <div class="col-xl-8">
            <x-empresas.reservas.comprador :habilitado="$puedeAgregarPasajeros" />
            <x-empresas.reservas.pasajero :habilitado="$puedeAgregarPasajeros" />
            <x-empresas.reservas.pasajeros-cotizacion :pasajeros="$pasajeros" :precio="$precio" />
            <x-empresas.reservas.pagos-taquilla :cuentas="$cuentas" :pagos="$pagos" :can-confirm="$canConfirm"
                :cambio="$cambio" :habilitado="$puedeAgregarPagos" />
        </div>

        <div class="col-xl-4" x-ref="panelColumna">
            <div x-ref="panelVenta" :style="panelStyle">
                <div style="overflow-y: auto; min-height: 0;">
                <x-empresas.reservas.transporte-taquilla :tarifa="$tarifa" />
                <x-empresas.reservas.resumen-taquilla :precio="$precio" :cantidad="$cantidad"
                    :pasajeros="count($pasajeros)" :total="$total" :abonado="$abonado" />

                </div>

                <form id="registrar-taquilla" x-ref="registroForm" @submit.prevent="registrar" novalidate class="card flex-shrink-0 mb-0">
                    <div class="card-body">
                        <p class="small text-muted">La reserva y los asientos se confirman al registrar. Las ventas por taquilla no tienen tasa de servicio.</p>
                        <button type="submit" class="btn btn-success w-100" :disabled="saving || !$el.dataset.ready" wire:loading.attr="disabled"
                            data-ready="{{ $canConfirm && $puedeAgregarPagos && count($pagos) > 0 && round($total - $abonado, 2) === 0.0 ? '1' : '' }}"
                            @disabled(! $canConfirm || ! $puedeAgregarPagos || count($pagos) === 0 || round($total - $abonado, 2) !== 0.0)>
                            Registrar reserva
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <x-empresas.reservas.confirmacion-taquilla :tarifa="$tarifa" :pasajeros="$pasajeros"
        :precio="$precio" :total="$total" :abonado="$abonado" :cambio="$cambio" />

    <x-layout.loader.fullpage wire:loading.delay.short />
</div>

@script
    <script>
        Alpine.data('saveReserva', () => {
            let validator = null;
            const temporizadores = {};
            return {
                saving: false,
                resaltados: { pasajeros: false, pagos: false, resumen: false },
                cleanup: null,
                panelStyle: '',
                panelObserver: null,
                ajustarPanel: null,

                init() {
                    this.cleanup = Livewire.on('successEventList', data => this.$store.toast.success(data.message));
                    this.ajustarPanel = () => this.posicionarPanel();
                    this.$nextTick(() => {
                        this.panelObserver = new ResizeObserver(this.ajustarPanel);
                        this.panelObserver.observe(this.$refs.panelColumna);
                        window.addEventListener('scroll', this.ajustarPanel, true);
                        window.addEventListener('resize', this.ajustarPanel);
                        this.posicionarPanel();
                    });
                },

                resaltarCambios(secciones) {
                    secciones.forEach(seccion => {
                        clearTimeout(temporizadores[seccion]);
                        this.resaltados[seccion] = true;
                        temporizadores[seccion] = setTimeout(() => {
                            this.resaltados[seccion] = false;
                        }, 3000);
                    });
                },

                posicionarPanel() {
                    if (window.innerWidth < 1200) {
                        this.panelStyle = '';
                        return;
                    }
                    const columna = this.$refs.panelColumna;
                    const rect = columna.getBoundingClientRect();
                    const css = getComputedStyle(columna);
                    const margen = Math.max(16, (document.querySelector('.app-header')?.getBoundingClientRect().bottom ?? 0) + 16);
                    const top = Math.max(margen, rect.top);
                    const left = rect.left + parseFloat(css.paddingLeft);
                    const width = rect.width - parseFloat(css.paddingLeft) - parseFloat(css.paddingRight);
                    this.panelStyle = `position: fixed; top: ${top}px; left: ${left}px; width: ${width}px; max-height: calc(100dvh - ${top + 16}px); display: flex; flex-direction: column; z-index: 10;`;
                },

                async registrar() {
                    if (this.saving) return;
                    const form = this.$refs.registroForm;
                    validator?.destroy();
                    validator = new JustValidate(form, {
                        errorLabelCssClass: ['invalid-feedback'],
                        errorFieldCssClass: ['is-invalid'],
                    });
                    Array.from(form.elements).filter(field => ['INPUT', 'SELECT'].includes(field.tagName)).forEach(field => {
                        const rules = [];
                        if (field.required) rules.push({ rule: 'required', errorMessage: 'Este campo es obligatorio.' });
                        if (field.type === 'email') rules.push({ rule: 'email', errorMessage: 'Ingresa un correo válido.' });
                        if (rules.length) validator.addField(field, rules);
                    });
                    validator.isSubmitted = true;
                    this.saving = true;
                    try {
                        if (await validator.revalidate()) {
                            validator.destroy();
                            validator = null;
                            const resumen = document.createElement('div');
                            resumen.innerHTML = this.$refs.confirmacionReserva.innerHTML;
                            resumen.querySelector('[data-comprador-nombre]').textContent = this.$wire.comprador.nombre;
                            resumen.querySelector('[data-comprador-telefono]').textContent = this.$wire.comprador.telefono;
                            const confirmacion = await Swal.fire({
                                title: 'Confirmar reserva',
                                html: resumen,
                                width: 720,
                                buttonsStyling: false,
                                showCloseButton: true,
                                showCancelButton: true,
                                confirmButtonText: 'Confirmar pago y registrar',
                                cancelButtonText: 'Volver a revisar',
                                reverseButtons: true,
                                customClass: {
                                    popup: 'rounded-4 shadow-lg p-0 overflow-hidden',
                                    title: 'text-start fs-4 px-4 pt-4 pb-0 m-0',
                                    htmlContainer: 'text-start m-0 p-4',
                                    actions: 'd-flex flex-wrap justify-content-end gap-2 w-100 border-top m-0 p-3 bg-light',
                                    confirmButton: 'btn btn-primary m-0',
                                    cancelButton: 'btn btn-outline-secondary m-0',
                                },
                            });
                            if (confirmacion.isConfirmed) {
                                await this.$wire.registrar();
                            }
                        }
                    } finally {
                        this.saving = false;
                    }
                },

                destroy() {
                    Object.values(temporizadores).forEach(clearTimeout);
                    validator?.destroy();
                    this.cleanup?.();
                    this.panelObserver?.disconnect();
                    window.removeEventListener('scroll', this.ajustarPanel, true);
                    window.removeEventListener('resize', this.ajustarPanel);
                },
            };
        });
    </script>
@endscript
