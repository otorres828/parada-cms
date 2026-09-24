{{--
    DESCRIPCIÓN DE USUARIO DE EMPRESA | Presenta identidad, acceso y estado del colaborador.
--}}

@props(['usuarioEmpresa'])

<div class="card-body">

    <dl class="row mb-0">
        <dt class="col-sm-4">Nombre</dt>
        <dd class="col-sm-8">
            {{ $usuarioEmpresa->nombre ?? '—' }}
        </dd>
        <dt class="col-sm-4">Correo</dt>
        <dd class="col-sm-8">
            {{ $usuarioEmpresa->email ?? '—' }}
        </dd>
        <dt class="col-sm-4">Administrador</dt>
        <dd class="col-sm-8">
            {{ $usuarioEmpresa->es_admin ? 'Sí' : 'No' }}
        </dd>
        <dt class="col-sm-4">Estado</dt>
        <dd class="col-sm-8">
            <x-list.status-badge :status="$usuarioEmpresa->estatus" />
        </dd>
    </dl>

</div>

