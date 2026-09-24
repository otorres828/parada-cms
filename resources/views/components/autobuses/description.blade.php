{{--
    DESCRIPCIÓN DE AUTOBÚS | Presenta empresa, placa, modelo, capacidad y amenidades.
--}}

@props(['autobus'])

<div class="card-body">

    <dl class="row mb-0">

        <dt class="col-sm-4">Empresa</dt>

        <dd class="col-sm-8">
            {{ $autobus->empresa?->nombre ?? '—' }}
        </dd>

        <dt class="col-sm-4">Placa</dt>

        <dd class="col-sm-8">
            {{ $autobus->placa ?? '—' }}
        </dd>

        <dt class="col-sm-4">Modelo</dt>

        <dd class="col-sm-8">
            {{ $autobus->modelo ?? '—' }}
        </dd>

        <dt class="col-sm-4">Asientos</dt>

        <dd class="col-sm-8">
            {{ $autobus->total_asientos ?? '—' }}
        </dd>

        <dt class="col-sm-4">Estado</dt>

        <dd class="col-sm-8">
            <x-list.status-badge :status="$autobus->estatus" />
        </dd>

    </dl>

</div>

