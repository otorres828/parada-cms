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
    </dl>

</div>

