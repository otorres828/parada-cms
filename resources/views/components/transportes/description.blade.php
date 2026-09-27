{{--
    DESCRIPCIÓN DE TRANSPORTE | Presenta empresa, placa, modelo, capacidad y amenidades.
--}}

@props(['transporte'])

<div class="card-body">

    <dl class="row mb-0">
        <dt class="col-sm-4">Tipo de transporte</dt>
        <dd class="col-sm-8">{{ $transporte->getTipoTransporte() }}</dd>

        <dt class="col-sm-4">Empresa</dt>

        <dd class="col-sm-8">
            {{ $transporte->empresa?->nombre ?? '—' }}
        </dd>

        <dt class="col-sm-4">Placa</dt>

        <dd class="col-sm-8">
            {{ $transporte->placa ?? '—' }}
        </dd>

        <dt class="col-sm-4">Modelo</dt>

        <dd class="col-sm-8">
            {{ $transporte->modelo ?? '—' }}
        </dd>

        <dt class="col-sm-4">Asientos</dt>

        <dd class="col-sm-8">
            {{ $transporte->total_asientos ?? '—' }}
        </dd>

        <dt class="col-sm-4">Estado</dt>

        <dd class="col-sm-8">
            <x-list.status-badge :status="$transporte->estatus" />
        </dd>

        <dt class="col-sm-4">Amenidades</dt>
        <dd class="col-sm-8">
            @forelse ($transporte->amenidades as $amenidad)
                <span class="badge text-bg-light border me-1 mb-1">{{ $amenidad->nombre }}</span>
            @empty
                Sin amenidades registradas
            @endforelse
        </dd>
    </dl>

</div>
