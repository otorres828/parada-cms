@props([
    'programacion',
    'capacidad',
    'pasajesPagados',
    'pasajesPendientes',
    'canViajesDetail',
    'canTransportesDetail',
])

<div class="card h-100">
    <div class="card-body">
        <dl class="row mb-0">
            <dt class="col-sm-4">Empresa</dt>
            <dd class="col-sm-8">{{ $programacion->viaje?->empresa?->nombre ?? '—' }}</dd>

            <dt class="col-sm-4">Ruta Principal</dt>
            <dd class="col-sm-8">
                @if ($canViajesDetail && $programacion->viaje_id)
                    <a href="{{ route('admin.viajes.detail', $programacion->viaje_id) }}" wire:navigate
                        class="fw-bold text-decoration-none">
                        {{ $programacion->viaje?->origenTerminal?->nombre ?? '—' }} →
                        {{ $programacion->viaje?->destinoTerminal?->nombre ?? '—' }}
                        <i class="bi bi-box-arrow-up-right ms-1 text-primary small"></i>
                    </a>
                @else
                    {{ $programacion->viaje?->origenTerminal?->nombre ?? '—' }} →
                    {{ $programacion->viaje?->destinoTerminal?->nombre ?? '—' }}
                @endif
            </dd>

            <dt class="col-sm-4">Fecha & Hora</dt>
            <dd class="col-sm-8">
                {{ $programacion->fecha_salida?->format('d/m/Y') ?? '—' }} a las
                {{ $programacion->hora_salida ? substr($programacion->hora_salida, 0, 5) : '—' }}
            </dd>

            <dt class="col-sm-4">Estado</dt>
            <dd class="col-sm-8"><x-list.status-badge :status="$programacion->estatus" /></dd>

            <hr class="col-12 my-3">

            <dt class="col-sm-4">Transporte</dt>
            <dd class="col-sm-8">
                @if ($canTransportesDetail && $programacion->transporte_id)
                    <a href="{{ route('admin.transportes.detail', $programacion->transporte_id) }}" wire:navigate>
                        {{ $programacion->transporte?->placa ?? '—' }}
                    </a>
                @else
                    {{ $programacion->transporte?->placa ?? '—' }}
                @endif
            </dd>

            <dt class="col-sm-4">Modelo</dt>
            <dd class="col-sm-8">{{ $programacion->transporte?->modelo ?? '—' }}</dd>

            <dt class="col-sm-4">Tipo de asiento</dt>
            <dd class="col-sm-8">{{ $programacion->transporte?->tipo_asiento ?? '—' }}</dd>

            <dt class="col-sm-4">Capacidad</dt>
            <dd class="col-sm-8">{{ $capacidad }} asientos</dd>

            <dt class="col-sm-4">Amenidades</dt>
            <dd class="col-sm-8">
                {{ $programacion->transporte?->amenidades->pluck('nombre')->implode(', ') ?: 'Sin amenidades' }}
            </dd>

            <hr class="col-12 my-3">

            <dt class="col-sm-4">Pasajes pagados</dt>
            <dd class="col-sm-8">
                {{ $pasajesPagados['cantidad'] }} pasajes /
                <x-money.dual :usd="$pasajesPagados['monto']" :bs="$pasajesPagados['monto_bs']" />
            </dd>

            <dt class="col-sm-4">Pasajes pendientes</dt>
            <dd class="col-sm-8">
                {{ $pasajesPendientes['cantidad'] }} pasajes /
                <x-money.dual :usd="$pasajesPendientes['monto']" :bs="$pasajesPendientes['monto_bs']" />
            </dd>

            <dt class="col-sm-4">Total tasas de servicio pagadas</dt>
            <dd class="col-sm-8">
                <x-money.dual :usd="$pasajesPagados['tasas']" :bs="$pasajesPagados['tasas_bs']" />
            </dd>

            <dt class="col-sm-4">Total tasas de servicio pendientes</dt>
            <dd class="col-sm-8">
                <x-money.dual :usd="$pasajesPendientes['tasas']" :bs="$pasajesPendientes['tasas_bs']" />
            </dd>
        </dl>
    </div>
</div>
