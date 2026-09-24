{{--
    DESCRIPCIÓN DE CLIENTE | Presenta identidad, contacto, estado y fecha de registro.
--}}

@props(['user'])

<div class="card-body">

    <dl class="row mb-0">
        <dt class="col-sm-4">Nombre</dt>
        <dd class="col-sm-8">
            {{ $user->name ?? '—' }}
        </dd>
        <dt class="col-sm-4">Apellido</dt>
        <dd class="col-sm-8">
            {{ $user->lastname ?? '—' }}
        </dd>
        <dt class="col-sm-4">Correo</dt>
        <dd class="col-sm-8">
            {{ $user->email ?? '—' }}
        </dd>
        <dt class="col-sm-4">Teléfono</dt>
        <dd class="col-sm-8">
            {{ $user->telefono ?? '—' }}
        </dd>
        <dt class="col-sm-4">Estado</dt>
        <dd class="col-sm-8">
            <x-list.status-badge :status="$user->status" />
        </dd>
    </dl>

</div>

