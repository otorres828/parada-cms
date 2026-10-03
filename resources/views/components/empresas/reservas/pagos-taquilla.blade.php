{{-- Pagos temporales con conversión al cambio vigente; no persisten hasta registrar. --}}
@props(['cuentas', 'pagos', 'canConfirm', 'cambio', 'habilitado' => false])
<div class="card mb-3"
    :class="{ 'border-primary': resaltados.pagos }"
    style="transition: border-color 250ms ease;" wire:key="formulario-pagos" x-data="pagosTaquilla({ datos: $wire.entangle('pago') })"
    data-tasa="{{ $cambio?->valor_usd ?? 0 }}">

    <div class="card-header">4. Pagos recibidos</div>

    <form class="card-body" @submit.prevent="enviar" x-ref="pagosForm" novalidate>
        @unless ($habilitado)
            <p class="text-muted small">Selecciona una salida y agrega pasajeros con asiento para ingresar los pagos.</p>
        @endunless

        <fieldset class="border-0 p-0 m-0" @disabled(! $habilitado)>
            
            <div class="row g-3">

                <div class="col-md-4">
                    <label class="form-label" for="taquilla-pago-tipo">Método</label>
                    <select id="taquilla-pago-tipo" class="form-select" x-model.number="datos.tipo">
                        <option value="3">Pago móvil</option>
                        @if ($canConfirm)
                            <option value="2">Efectivo</option>
                            <option value="4">Tarjeta</option>
                        @endif
                    </select>
                </div>
               
                <div class="col-md-4">
                    <label class="form-label" for="taquilla-pago-usd">Monto en dólares</label>
                    <div class="input-group">
                        <span class="input-group-text">$</span>
                        <input id="taquilla-pago-usd" type="number" min="0.01" step="0.01" class="form-control"
                            x-model="dolares" @input="convertir('USD', $event.target.value)" required>
                    </div>
                </div>

                 <div class="col-md-4">
                    <label class="form-label" for="taquilla-pago-bs">Monto en bolívares</label>
                    <div class="input-group">
                        <span class="input-group-text">Bs</span>
                        <input id="taquilla-pago-bs" type="number" min="0.01" step="0.01" class="form-control"
                            x-model="bolivares" @input="convertir('VES', $event.target.value)" required>
                    </div>
                </div>

                <div class="col-md-6" x-show="Number(datos.tipo) === 3" x-cloak>
                    <label class="form-label" for="taquilla-pago-cuenta">Cuenta receptora</label>
                    <select id="taquilla-pago-cuenta" class="form-select" x-model="datos.cuenta_id"
                        :disabled="Number(datos.tipo) !== 3" :required="Number(datos.tipo) === 3">
                        <option value="">Seleccionar cuenta</option>
                        @foreach ($cuentas->where('tipo', 1) as $cuenta)
                            <option value="{{ $cuenta->id }}">{{ $cuenta->banco }} · {{ $cuenta->numero_cuenta_telefono }} · {{ $cuenta->getTipoDocumento() }}-{{ $cuenta->numero_documento }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6" x-show="Number(datos.tipo) !== 2" x-cloak>
                    <label class="form-label" for="taquilla-pago-referencia">Referencia</label>
                    <input id="taquilla-pago-referencia" class="form-control" x-model="datos.referencia" maxlength="255"
                        :disabled="Number(datos.tipo) === 2" :required="Number(datos.tipo) !== 2">
                </div>
            </div>

            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mt-3">
                <p class="text-muted small mb-0">1 dólar = {{ number_format((float) $cambio?->valor_usd, 2) }} Bs.<br>
                    {{ $canConfirm ? 'Al confirmar la venta, todos los pagos se registran como recibidos.' : 'Necesitas permiso para confirmar cobros y completar esta venta.' }}</p>
                <button type="submit" class="btn btn-primary" :disabled="saving" wire:loading.attr="disabled">Agregar pago</button>
            </div>
        </fieldset>
    </form>

    <ul class="list-group list-group-flush">
        @forelse ($pagos as $indice => $pago)
            @php
                $usd = $pago['moneda'] === 'VES'
                    ? round((float) $pago['monto'] / max(0.01, (float) $cambio?->valor_usd), 2)
                    : (float) $pago['monto'];
                $bs = $pago['moneda'] === 'VES' ? (float) $pago['monto'] : round($usd * (float) $cambio?->valor_usd, 2);
            @endphp
            <li class="list-group-item d-flex justify-content-between align-items-center gap-3" wire:key="pago-cotizado-{{ $indice }}">
                <span>
                    <strong>{{ (new \App\Models\PagoReserva(['tipo_pago' => $pago['tipo']]))->getNameTipoPago() }}</strong>
                    · USD {{ number_format($usd, 2, '.', ',') }} / BS {{ number_format($bs, 2, ',', '.') }}
                    @if (! empty($pago['referencia']))
                        <small class="d-block text-muted">Referencia: {{ $pago['referencia'] }}</small>
                    @endif
                </span>
                <button type="button" class="btn btn-sm btn-outline-danger" wire:click="removerPago({{ $indice }})">Retirar</button>
            </li>
        @empty
            <li class="list-group-item text-muted small">No has agregado pagos.</li>
        @endforelse
    </ul>
</div>

@script
    <script>
        Alpine.data('pagosTaquilla', (estado) => {
            let validator = null;
            return {
                ...estado,
                saving: false,
                dolares: '',
                bolivares: '',

                init() {
                    this.$watch('datos.monto', value => {
                        if (value === '') {
                            this.dolares = '';
                            this.bolivares = '';
                        }
                    });
                },

                convertir(moneda, valor) {
                    const tasa = Number(this.$root.dataset.tasa);
                    this.datos.moneda = moneda;
                    this.datos.monto = valor;
                    if (moneda === 'USD') {
                        this.bolivares = valor !== '' && tasa > 0 ? (Number(valor) * tasa).toFixed(2) : '';
                    } else {
                        this.dolares = valor !== '' && tasa > 0 ? (Number(valor) / tasa).toFixed(2) : '';
                    }
                },

                async enviar() {
                    if (this.saving) return;
                    validator?.destroy();
                    validator = new JustValidate(this.$refs.pagosForm, {
                        errorLabelCssClass: ['invalid-feedback'],
                        errorFieldCssClass: ['is-invalid'],
                    });
                    this.$refs.pagosForm.querySelectorAll('[required]').forEach(field => {
                        if (!field.disabled) {
                            validator.addField(field, [{ rule: 'required', errorMessage: 'Este campo es obligatorio.' }]);
                        }
                    });
                    validator.isSubmitted = true;
                    this.saving = true;
                    try {
                        if (await validator.revalidate()) {
                            validator.destroy();
                            validator = null;
                            await this.$wire.agregarPago();
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
