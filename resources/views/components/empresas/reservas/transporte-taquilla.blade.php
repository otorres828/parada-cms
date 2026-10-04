{{-- Transporte asignado a la salida elegida, con sus amenidades. --}}
@props(['tarifa'])
<div class="card mb-3">
    <div class="card-body">
        <h2 class="h5">Transporte de la salida</h2>
        @if ($tarifa)
            @php($transporte = $tarifa->programacion->transporte)
            <h3 class="h6 mb-3">{{ $tarifa->origenTerminal->nombre }} → {{ $tarifa->destinoTerminal->nombre }}</h3>
            <dl class="row mb-2">
                <dt class="col-5">Salida del tramo</dt>
                <dd class="col-7">{{ $tarifa->getSalida()?->format('d/m/Y H:i') ?? 'Sin horario' }}</dd>
                <dt class="col-5">Transporte</dt>
                <dd class="col-7">{{ $transporte->getTipoTransporte() }} · {{ $transporte->modelo }}</dd>
                <dt class="col-5">Placa</dt>
                <dd class="col-7">{{ $transporte->placa }}</dd>
                <dt class="col-5">Asientos</dt>
                <dd class="col-7">{{ $transporte->total_asientos }} · {{ $transporte->tipo_asiento }}</dd>
            </dl>
            <div class="d-flex flex-wrap gap-2">
                @forelse ($transporte->amenidades->where('estatus', 1) as $amenidad)
                    <span class="badge bg-light text-dark border">{{ $amenidad->nombre }}</span>
                @empty
                    <span class="small text-muted">Sin amenidades registradas.</span>
                @endforelse
            </div>
        @else
            <p class="text-muted mb-0">Aquí verás el transporte y sus amenidades al seleccionar una salida.</p>
        @endif
    </div>
</div>
