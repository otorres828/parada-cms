{{-- Transporte asignado a la salida elegida, con sus amenidades. --}}
@props(['tarifa', 'disponibles', 'cantidad'])

<div class="card mb-3">
    <div class="card-header">Transporte de la salida</div>
    <div class="card-body">
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

            <div class="border rounded p-3 my-3" role="status" aria-live="polite">
                <div class="d-flex justify-content-between align-items-center gap-2">
                    <span>Puestos disponibles en el tramo</span>
                    <strong class="{{ $disponibles > 0 ? 'text-success' : 'text-danger' }}">{{ $disponibles }}</strong>
                </div>
                @if ($cantidad > 0)
                    <div class="small text-muted mt-2">
                        En esta venta: {{ $cantidad }} · Quedarían: {{ max(0, $disponibles - $cantidad) }}
                    </div>
                    @if ($cantidad > $disponibles)
                        <div class="small text-danger mt-2">Los pasajeros con asiento superan la disponibilidad del tramo.</div>
                    @endif
                @endif
                <div class="small text-muted mt-2">Los puestos se confirman al registrar la reserva.</div>
            </div>

        @else
            <p class="text-muted mb-0">Aquí verás el transporte y sus amenidades al seleccionar una salida.</p>
        @endif
    </div>
</div>
