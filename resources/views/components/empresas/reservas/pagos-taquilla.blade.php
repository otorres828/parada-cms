{{-- Cada pago permanece en pantalla hasta registrar toda la venta. --}}
@props(['cuentas', 'pagos', 'canConfirm', 'cambio'])
<div class="card mb-3">
    <div class="card-header">Pagos recibidos</div>
    <form class="card-body" x-data="formTaquilla('agregarPago')" @submit.prevent="enviar" x-ref="form" novalidate>
        <div class="row g-3 align-items-end">
            <div class="col-md-2"><label class="form-label">Método</label>
                <select class="form-select" wire:model.live.number="pago.tipo">
                    <option value="3">Pago móvil</option>
                    @if ($canConfirm)
                        <option value="2">Efectivo</option>
                        <option value="4">Tarjeta</option>
                    @endif
                </select>
            </div>
            <div class="col-md-2"><label class="form-label">Moneda</label>
                <select class="form-select" wire:model="pago.moneda">
                    <option value="VES">Bolívares</option>
                    <option value="USD">Dólares</option>
                </select>
            </div>
            <div class="col-md-2"><label class="form-label">Monto recibido</label><input type="number" min="0.01"
                    step="0.01" class="form-control" wire:model="pago.monto" required></div>
            @if ((int) $this->pago['tipo'] !== 2)
                <div class="col-md-3"><label class="form-label">Cuenta receptora</label>
                    <select class="form-select" wire:model="pago.cuenta_id">
                        <option value="">Seleccionar cuenta</option>
                        @foreach ($cuentas as $cuenta)
                            @if ($cuenta->tipo === ((int) $this->pago['tipo'] === 3 ? 1 : 2))
                                <option value="{{ $cuenta->id }}">{{ $cuenta->banco }} ·
                                    {{ $cuenta->numero_cuenta_telefono }}</option>
                            @endif
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2"><label class="form-label">Referencia</label><input class="form-control"
                        wire:model="pago.referencia"></div>
            @endif
            <div class="col-md-auto"><button type="submit" class="btn btn-primary" wire:loading.attr="disabled">Agregar
                    pago</button></div>
        </div>
        <p class="text-muted small mt-2 mb-0">Conversión: {{ $cambio?->valor_usd }} Bs por dólar. El pago móvil queda
            pendiente de verificación.</p>
    </form>
    <ul class="list-group list-group-flush">
        @foreach ($pagos as $indice => $pago)
            <li class="list-group-item d-flex justify-content-between align-items-center"
                wire:key="pago-cotizado-{{ $indice }}">
                <span>{{ \App\Models\PagoReserva::NAME_TIPO_PAGO[$pago['tipo']] }} · {{ $pago['moneda'] }}
                    {{ number_format((float) $pago['monto'], 2) }} · {{ $pago['referencia'] ?? '' }}</span>
                <button type="button" class="btn btn-sm btn-outline-danger"
                    wire:click="removerPago({{ $indice }})">Retirar</button>
            </li>
        @endforeach
    </ul>
</div>
