{{--
    DESCRIPCIÓN DE RESERVA | Presenta ruta, cliente, cupón, importes y estado de pago.
--}}

@props(['reserva', 'canViewCampaign', 'canViewPassengers'])

<div class="card-body">

    <dl class="row mb-0">
        <dt class="col-sm-4">Referencia</dt>
        <dd class="col-sm-8">
            {{ $reserva->codigo_referencia ?? '—' }}
        </dd>

        <dt class="col-sm-4">Ruta</dt>

        <dd class="col-sm-8">
            {{ $reserva?->origenTerminal?->nombre ?? 'No registrado' }} - {{ $reserva?->destinoTerminal?->nombre ?? 'No registrado' }}
        </dd>

        <dt class="col-sm-4">Programación</dt>
        <dd class="col-sm-8">

            @if ($canViewPassengers)
                <a href="{{ route('admin.programaciones.passengers', $reserva->programacion_id) }}"
                    wire:navigate>
                    Ver programación #{{ $reserva->programacion_id }}
                </a>
            @else
                #{{ $reserva->programacion_id }}
            @endif

        </dd>

        <hr class="col-12 my-3">

        <dt class="col-sm-4">Cliente</dt>
        <dd class="col-sm-8">
            {{ $reserva->usuario?->name ?? '—' }}
        </dd>
        <dt class="col-sm-4">Empresa</dt>
        <dd class="col-sm-8">
            {{ $reserva->programacion?->viaje?->empresa?->nombre ?? '—' }}
        </dd>
        <dt class="col-sm-4">Fecha</dt>
        <dd class="col-sm-8">
            {{ $reserva->fecha_compra?->format('d/m/Y H:i') ?? '—' }}
        </dd>

        @if ($reserva->cupon)
            <hr class="col-12 my-3">

            <dt class="col-sm-4">Cupón aplicado</dt>
            <dd class="col-sm-8">
                @if ($canViewCampaign)
                    <a href="{{ route('admin.cupones.detail', $reserva->cupon->configuracion_cupon_id) }}"
                        wire:navigate>
                        {{ $reserva->cupon->codigo }}
                    </a>
                @else
                    {{ $reserva->cupon->codigo }}
                @endif
            </dd>
        @endif

        <hr class="col-12 my-3">

        <dt class="col-sm-4">Total</dt>
        <dd class="col-sm-8">
            {{ number_format($reserva->monto_total ?? 0, 2) }}
        </dd>
        <dt class="col-sm-4">Estado</dt>
        <dd class="col-sm-8">
            <x-list.status-reserva :status="$reserva->estado_pago" />
        </dd>
        <dt class="col-sm-4">Tasa de servicio</dt>
        <dd class="col-sm-8">{{ number_format($reserva->tasa_servicio, 2) }}</dd>
    </dl>

</div>

