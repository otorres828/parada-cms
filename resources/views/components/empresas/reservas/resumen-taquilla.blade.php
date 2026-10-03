{{-- Resumen de importes de la venta, sin persistencia. --}}
@props(['precio', 'cantidad', 'pasajeros', 'total', 'abonado'])
<div class="card mb-3"
    :class="{ 'border-primary': resaltados.resumen }"
    style="transition: border-color 250ms ease;">
    <div class="card-body">
        <h2 class="h5">Resumen de la venta</h2>
        <dl class="row mb-0">
            <dt class="col-8">Precio del pasaje</dt>
            <dd class="col-4 text-end">${{ number_format($precio, 2) }}</dd>
            <dt class="col-8">Cantidad de pasajeros</dt>
            <dd class="col-4 text-end">{{ $pasajeros }} ({{ $cantidad }} con asiento)</dd>
            <dt class="col-8">Subtotal</dt>
            <dd class="col-4 text-end">${{ number_format($total, 2) }}</dd>
            <dt class="col-8">Descuento</dt>
            <dd class="col-4 text-end">$0.00</dd>
            <dt class="col-8">Tasa de servicio</dt>
            <dd class="col-4 text-end">$0.00</dd>
            <dt class="col-8 border-top pt-2">Total</dt>
            <dd class="col-4 text-end border-top pt-2 fw-bold">${{ number_format($total, 2) }}</dd>
            <dt class="col-8">Pagos agregados</dt>
            <dd class="col-4 text-end">${{ number_format($abonado, 2) }}</dd>
            <dt class="col-8">{{ $abonado > $total ? 'Excedente por corregir' : 'Pendiente por completar' }}</dt>
            <dd class="col-4 text-end">${{ number_format(abs($total - $abonado), 2) }}</dd>
        </dl>
    </div>
</div>
