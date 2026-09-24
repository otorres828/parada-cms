@props(['solicitud'])

<div class="card-body">

    <dl class="row mb-0">

        <dt class="col-sm-4">Empresa</dt>
        <dd class="col-sm-8">{{ $solicitud->empresa }}</dd>

        <dt class="col-sm-4">Representante</dt>
        <dd class="col-sm-8">{{ $solicitud->nombre }}</dd>

        <dt class="col-sm-4">Cargo</dt>
        <dd class="col-sm-8">{{ $solicitud->cargo }}</dd>

        <dt class="col-sm-4">Teléfono</dt>
        <dd class="col-sm-8">
            <a href="tel:{{ $solicitud->telefono }}">{{ $solicitud->telefono }}</a>
        </dd>

        <dt class="col-sm-4">Correo</dt>
        <dd class="col-sm-8">
            <a href="mailto:{{ $solicitud->email }}">{{ $solicitud->email }}</a>
        </dd>

        <dt class="col-sm-4">Ciudad</dt>
        <dd class="col-sm-8">{{ $solicitud->ciudad ?: '—' }}</dd>

        <dt class="col-sm-4">Recibida</dt>
        <dd class="col-sm-8">{{ $solicitud->created_at?->format('d/m/Y H:i') }}</dd>

    </dl>

    <hr>

    <h6>Mensaje</h6>
    <p class="mb-0 text-break" style="white-space: pre-line;">{{ $solicitud->mensaje ?: 'Sin mensaje adicional.' }}</p>

</div>
