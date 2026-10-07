{{--
    DESCRIPCIÓN DE CAMPAÑA | Presenta condiciones, alcance, vigencia y estado del cupón.
--}}

@props(['configuracionCupon','showEmpresa'=>true])

<div class="card-body">

    <dl class="row mb-0">

        <dt class="col-sm-4">Campaña</dt>
        <dd class="col-sm-8">
            {{ $configuracionCupon->nombre_campana ?? '—' }}
        </dd>

        @if ($showEmpresa)
            <dt class="col-sm-4">Empresa</dt>
            <dd class="col-sm-8">
                {{ $configuracionCupon->empresa?->nombre ?? '—' }}
            </dd>
        @endif

        <dt class="col-sm-4">Descuento</dt>
        <dd class="col-sm-8">
            {{ $configuracionCupon->tipo_descuento === 'porcentaje' ? 'Porcentaje' : 'Monto fijo' }}
        </dd>

     <dt class="col-sm-4">Modalidad</dt>
        <dd class="col-sm-8">
            {{ match ($configuracionCupon->modalidad) {
                'PRIMERA_COMPRA' => 'Primera compra',
                default => 'General',
            } }}
        </dd>

        <dt class="col-sm-4">Aplica en</dt>
        <dd class="col-sm-8">
            {{ $configuracionCupon->aplica_en === 'pasajes' ? 'Cada pasaje' : 'Reserva general' }}
        </dd>

        <dt class="col-sm-4">Valor</dt>
        <dd class="col-sm-8">
            {{ number_format($configuracionCupon->monto_descuento ?? 0, 2) }}
        </dd>

        <dt class="col-sm-4">Fecha Inicio</dt>
        <dd class="col-sm-8">
            {{ $configuracionCupon->fecha_inicio?->format('d/m/Y H:i') ?? '—' }}
        </dd>

        <dt class="col-sm-4">Fecha Fin</dt>
        <dd class="col-sm-8">
            {{ $configuracionCupon->fecha_fin?->format('d/m/Y H:i') ?? '—' }}
        </dd>

        <dt class="col-sm-4">Estado</dt>
        <dd class="col-sm-8">
            <x-list.status-badge :status="$configuracionCupon->estatus" />
        </dd>

    </dl>

</div>

