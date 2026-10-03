{{--
    DESCRIPCIÓN DE PASAJE | Presenta reserva, viajero, cupón, importes y estado de abordaje.
--}}

@props(['pasaje', 'canViewCampaign', 'canViewReservation','viewTasaServicio'])

<div class="card-body">

    <dl class="row mb-0">

        <dt class="col-sm-4">Ruta</dt>

        <dd class="col-sm-8">
            {{ $pasaje->reserva?->origenTerminal?->nombre ?? 'No registrado' }} → {{ $pasaje->reserva?->destinoTerminal?->nombre ?? 'No registrado' }}
        </dd>

        <dt class="col-sm-4">Reserva</dt>

        <dd class="col-sm-8">
            @if ($pasaje->reserva && $canViewReservation)
                <a href="{{ route('empresas.reservas.detail', $pasaje->reserva_id) }}" wire:navigate>
                    {{ $pasaje->reserva->codigo_referencia }}
                </a>
            @else
                {{ $pasaje->reserva?->codigo_referencia ?? '—' }}
            @endif
        </dd>

        <dt class="col-sm-4">Fecha de reserva</dt>

        <dd class="col-sm-8">
            {{ $pasaje->reserva?->fecha_compra?->format('d/m/Y H:i') ?? '—' }}
        </dd>

        <dt class="col-sm-4">Salida del tramo</dt>

        <dd class="col-sm-8">
            {{ $pasaje->reserva?->tramoPrecio?->getSalida()?->format('d/m/Y H:i') ?? 'Sin horario' }}
        </dd>

        <dt class="col-sm-4">Llegada al destino</dt>

        <dd class="col-sm-8">{{ $pasaje->reserva?->tramoPrecio?->getLlegada()?->format('d/m/Y H:i') ?? 'Sin horario' }}</dd>

        <hr class="col-12 my-3">

        <dt class="col-sm-4">Viajero</dt>

        <dd class="col-sm-8">{{ $pasaje->viajero_nombre_completo ?: '—' }}</dd>

        <dt class="col-sm-4">Documento</dt>

        <dd class="col-sm-8">
            {{ $pasaje->viajero_documento ?? '—' }}
        </dd>

        <dt class="col-sm-4">Fecha de nacimiento</dt>

        <dd class="col-sm-8">
            {{ $pasaje->viajero_fecha_nacimiento?->format('d/m/Y') ?? '—' }}
        </dd>

        <dt class="col-sm-4">Asiento</dt>

        <dd class="col-sm-8">{{ $pasaje->numero_asiento ?? '—' }}</dd>

        <dt class="col-sm-4">Abordado</dt>

        <dd class="col-sm-8">
            {{ $pasaje->abordado ? 'Sí' : 'No' }}
            @if ($pasaje->abordado && $pasaje->hora_abordaje)
                a las {{ substr($pasaje->hora_abordaje, 0, 5) }}
            @endif
        </dd>

        @if ($pasaje->reserva?->cupon)
            <hr class="col-12 my-3">

            <dt class="col-sm-4">Cupón aplicado</dt>

            <dd class="col-sm-8">
                @if ($canViewCampaign)
                    <a href="{{ route('admin.cupones.detail', $pasaje->reserva->cupon->configuracion_cupon_id) }}"
                        wire:navigate>
                        {{ $pasaje->reserva->cupon->codigo }}
                    </a>
                @else
                    {{ $pasaje->reserva->cupon->codigo }}
                @endif
            </dd>
        @endif

        <hr class="col-12 my-3">

        <dt class="col-sm-4">Precio</dt>

        <dd class="col-sm-8"><x-money.dual :usd="$pasaje->precio_base" :bs="$pasaje->calcularMontoBs($pasaje->precio_base)" /></dd>

        <dt class="col-sm-4">Descuento</dt>

        <dd class="col-sm-8"><x-money.dual :usd="$pasaje->descuento" :bs="$pasaje->calcularMontoBs($pasaje->descuento)" /></dd>

        @if ($viewTasaServicio)
            
            <dt class="col-sm-4">Subtotal</dt>

            <dd class="col-sm-8"><x-money.dual :usd="$pasaje->subtotal" :bs="$pasaje->calcularMontoBs($pasaje->subtotal)" /></dd>

            <dt class="col-sm-4">Tasa de servicio</dt>

            <dd class="col-sm-8"><x-money.dual :usd="$pasaje->tasa_servicio" :bs="$pasaje->calcularMontoBs($pasaje->tasa_servicio)" /></dd>

            <dt class="col-sm-4">Total</dt>
            <dd class="col-sm-8"><x-money.dual :usd="$pasaje->total" :bs="$pasaje->calcularMontoBs($pasaje->total)" /></dd>

        @else

            <dt class="col-sm-4">Total</dt>

            <dd class="col-sm-8"><x-money.dual :usd="$pasaje->subtotal" :bs="$pasaje->calcularMontoBs($pasaje->subtotal)" /></dd>


        @endif


        <dt class="col-sm-4">Pago</dt>

        <dd class="col-sm-8">
            <x-list.status-reserva :status="$pasaje->reserva->estado_pago" />
        </dd>

        @if ($viewTasaServicio)
            
            <hr class="col-12 my-3">

            <dt class="col-sm-4">Tipo de tasa de servicio aplicada</dt>

            <dd class="col-sm-8">
                {{ $pasaje->tipo_servicio === 2 ? 'Porcentaje' : ($pasaje->tipo_servicio === 1 ? 'Monto fijo' : 'Registro histórico') }}
            </dd>

            <dt class="col-sm-4">Valor aplicado</dt>

            <dd class="col-sm-8">
                {{ $pasaje->valor !== null ? number_format($pasaje->valor, 2) . ($pasaje->tipo_servicio === 2 ? ' %' : '') : 'No registrado' }}
            </dd>
            
        @endif

    </dl>

</div>
