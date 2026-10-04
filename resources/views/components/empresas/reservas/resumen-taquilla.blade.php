{{-- Resumen de importes de la venta, sin persistencia. --}}
@props(['precio', 'cantidad', 'pasajeros', 'total', 'abonado', 'cambio'])
<div class="card mb-3"
    :class="{ 'border-primary': resaltados.resumen }"
    style="transition: border-color 250ms ease;">
    <div class="card-body">
        <h2 class="h5">Resumen de la venta</h2>
        <dl class="row mb-0">
            <dt class="col-6">Precio del pasaje</dt>
            <dd class="col-6 text-end">
                <span class="d-block">USD {{ number_format($precio, 2) }}</span>
                <small class="d-block text-muted">BS {{ number_format(($precio) * (float) $cambio?->valor_usd, 2, ',', '.') }}</small>
            </dd>
            <dt class="col-6">Cantidad de pasajeros</dt>
            <dd class="col-6 text-end">{{ $pasajeros }} ({{ $cantidad }} con asiento)</dd>
            <dt class="col-6">Subtotal</dt>
            <dd class="col-6 text-end">
                <span class="d-block">USD {{ number_format($total, 2) }}</span>
                <small class="d-block text-muted">BS {{ number_format(($total) * (float) $cambio?->valor_usd, 2, ',', '.') }}</small>
            </dd>
            <dt class="col-6">Descuento</dt>
            <dd class="col-6 text-end">
                <span class="d-block">USD 0.00</span>
                <small class="d-block text-muted">BS 0,00</small>
            </dd>
            <dt class="col-6">Tasa de servicio</dt>
            <dd class="col-6 text-end">
                <span class="d-block">USD 0.00</span>
                <small class="d-block text-muted">BS 0,00</small>
            </dd>
            <dt class="col-6 border-top pt-2">Total</dt>
            <dd class="col-6 text-end border-top pt-2 fw-bold">
                <span class="d-block">USD {{ number_format($total, 2) }}</span>
                <small class="d-block text-muted">BS {{ number_format(($total) * (float) $cambio?->valor_usd, 2, ',', '.') }}</small>
            </dd>
            <dt class="col-6">Pagos agregados</dt>
            <dd class="col-6 text-end">
                <span class="d-block">USD {{ number_format($abonado, 2) }}</span>
                <small class="d-block text-muted">BS {{ number_format(($abonado) * (float) $cambio?->valor_usd, 2, ',', '.') }}</small>
            </dd>
            <dt class="col-6">{{ $abonado > $total ? 'Excedente por corregir' : 'Pendiente por completar' }}</dt>
            <dd class="col-6 text-end">
                <span class="d-block">USD {{ number_format(abs($total - $abonado), 2) }}</span>
                <small class="d-block text-muted">BS {{ number_format((abs($total - $abonado)) * (float) $cambio?->valor_usd, 2, ',', '.') }}</small>
            </dd>
        </dl>
    </div>
</div>
