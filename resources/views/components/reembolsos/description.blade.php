{{--
    DESCRIPCIÓN DE REEMBOLSO | Presenta reserva, empresa, importe, estado y resolución.
--}}

@props(['reembolso'])

<div class="card-body">

    <dl class="row mb-0">
        <dt class="col-sm-4">Pago</dt>
        <dd class="col-sm-8">
            {{ $reembolso->pagoReserva?->referencia_pago ?? '—' }}
        </dd>
        <dt class="col-sm-4">Empresa</dt>
        <dd class="col-sm-8">
            {{ $reembolso->empresa?->nombre ?? '—' }}
        </dd>
        <dt class="col-sm-4">Monto</dt>
        <dd class="col-sm-8">
            {{ number_format($reembolso->monto ?? 0, 2) }}
        </dd>
        <dt class="col-sm-4">Estado</dt>
        <dd class="col-sm-8">
            <x-list.status-badge :status="$reembolso->estatus" />
        </dd>
        <dt class="col-sm-4">Solicitado</dt>
        <dd class="col-sm-8">
            {{ $reembolso->created_at?->format('d/m/Y H:i') ?? '—' }}
        </dd>
        <dt class="col-sm-4">Motivo</dt>
        <dd class="col-sm-8">{{ $reembolso->motivo ?? '—' }}</dd>
        <dt class="col-sm-4">Observaciones</dt>
        <dd class="col-sm-8">{{ $reembolso->comentario ?? '—' }}</dd>
        <dt class="col-sm-4">Fecha de resolución</dt>
        <dd class="col-sm-8">{{ $reembolso->fecha_resolucion?->format('d/m/Y H:i') ?? '—' }}</dd>
    </dl>

</div>

