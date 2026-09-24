{{--
    DESCRIPCIÓN DE AUDITORÍA | Presenta actor, acción, recurso y datos registrados.
--}}

@props(['auditoria'])

<div class="card-body">

    <dl class="row mb-0">
        <dt class="col-sm-4">Administrador</dt>

        <dd class="col-sm-8">{{ $auditoria->admin?->name ?? '—' }}</dd>

        <dt class="col-sm-4">Acción</dt>

        <dd class="col-sm-8">{{ $auditoria->accion ?? '—' }}</dd>

        <dt class="col-sm-4">Entidad</dt>

        <dd class="col-sm-8">{{ $auditoria->entidad ?? '—' }}</dd>

        <dt class="col-sm-4">Registro</dt>

        <dd class="col-sm-8">{{ $auditoria->entidad_id ?? '—' }}</dd>

        <dt class="col-sm-4">Fecha</dt>

        <dd class="col-sm-8">{{ $auditoria->created_at?->format('d/m/Y H:i') ?? '—' }}</dd>

    </dl>

</div>

