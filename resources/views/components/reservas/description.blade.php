{{--
    DESCRIPCIÓN DE RESERVA | Presenta ruta, cliente, cupón, importes y estado de pago.
--}}

@props(['reserva', 'canViewCampaign', 'canViewPassengers', 'canViewReservation'])

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

        <dt class="col-sm-4">Subtotal</dt>
        <dd class="col-sm-8">
            {{ number_format($reserva->monto_pasajes ?? 0, 2) }}
        </dd>

        <dt class="col-sm-4">Descuento</dt>
        <dd class="col-sm-8">
            {{ number_format($reserva->descuento_aplicado ?? 0, 2) }}
        </dd>

        <dt class="col-sm-4">Total</dt>
        <dd class="col-sm-8">
            {{ number_format(($reserva->monto_pasajes ?? 0) - ($reserva->descuento_aplicado ?? 0), 2) }}
        </dd>

        <dt class="col-sm-4">Tasa de servicio</dt>
        <dd class="col-sm-8">
            {{ number_format($reserva->tasa_servicio ?? 0, 2) }}
        </dd>

        <dt class="col-sm-4">Total + tasa de servicio</dt>
        <dd class="col-sm-8 fw-semibold">
            {{ number_format($reserva->monto_total ?? 0, 2) }}
        </dd>

        <hr class="col-12 my-3">

        <dt class="col-sm-4">Estado</dt>
        <dd class="col-sm-8">
            @if ($reserva->estado_pago === \App\Models\Reserva::ESTADO_PAGO_REPROGRAMADO && $reserva->reprogramado)
                <span class="badge text-bg-primary">Cancelada por reprogramación</span>

                @if ($canViewReservation)
                    <a class="ms-1" href="{{ route('admin.reservas.detail', $reserva->reprogramado->id) }}"
                        wire:navigate>
                        #{{ $reserva->reprogramado->id }}
                    </a>
                @else
                    <span class="ms-1">#{{ $reserva->reprogramado->id }}</span>
                @endif
            @elseif ($reserva->reprogramacion_id)
                <span class="badge text-bg-success">Pagada por reprogramación</span>

                @if ($canViewReservation)
                    <a class="ms-1" href="{{ route('admin.reservas.detail', $reserva->reprogramacion_id) }}"
                        wire:navigate>
                        #{{ $reserva->reprogramacion_id }}
                    </a>
                @else
                    <span class="ms-1">#{{ $reserva->reprogramacion_id }}</span>
                @endif
            @else
                <x-list.status-reserva :status="$reserva->estado_pago" />
            @endif
        </dd>
    </dl>

</div>

