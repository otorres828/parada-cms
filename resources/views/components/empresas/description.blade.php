{{--
    DESCRIPCIÓN DE EMPRESA | Presenta identificación, contacto, estado y datos operativos.
--}}

@props(['empresa'])

<div class="card-body">

    <dl class="row mb-0">
        <dt class="col-sm-4">Empresa</dt>
        <dd class="col-sm-8">
            {{ $empresa->nombre ?? '—' }}
        </dd>
        <dt class="col-sm-4">Identificación</dt>
        <dd class="col-sm-8">
            {{ $empresa->rif ?? '—' }}
        </dd>
        <dt class="col-sm-4">Correo</dt>
        <dd class="col-sm-8">
            {{ $empresa->email ?? '—' }}
        </dd>
        <dt class="col-sm-4">Gestión de pagos</dt>
        <dd class="col-sm-8">
            {{ $empresa->getTipoContrato() }}
        </dd>
        <dt class="col-sm-4">Estado</dt>
        <dd class="col-sm-8">
            <x-list.status-badge :status="$empresa->estatus" />
        </dd>
        @if ($empresa->tipo_contrato === \App\Models\Empresa::CONTRATO_ELLOS_RECIBEN)
            <dt class="col-sm-4">Corte semanal</dt>
            <dd class="col-sm-8">
                {{ $empresa->getDiaCorte() }} a las {{ substr($empresa->hora_corte, 0, 5) }}
            </dd>
            <dt class="col-sm-4">Cierre de pago</dt>
            <dd class="col-sm-8">
                {{ $empresa->getDiaVencimiento() }} a las {{ substr($empresa->hora_vencimiento, 0, 5) }}
            </dd>
            <dt class="col-sm-4">Estado de cobranza</dt>
            <dd class="col-sm-8">
                @if ($empresa->estaBloqueadaPorCobranza())
                    <span class="badge text-bg-danger">Suspendida desde {{ $empresa->bloqueada_por_cobranza_at->format('d/m/Y H:i') }}</span>
                @else
                    <span class="badge text-bg-success">Al día</span>
                @endif
            </dd>
        @endif
    </dl>

</div>

