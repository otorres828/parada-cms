@props(['status'])

@php
    $reservaEstado = new \App\Models\Reserva(['estado_pago' => $status]);
    $label = ucfirst($reservaEstado->getStatusPago());
    $color = match ($reservaEstado->estado_pago) {
        \App\Models\Reserva::ESTADO_PAGO_NUEVO => 'info',
        \App\Models\Reserva::ESTADO_PAGO_PAGADO => 'success',
        \App\Models\Reserva::ESTADO_PAGO_PENDIENTE => 'warning',
        \App\Models\Reserva::ESTADO_PAGO_CANCELADO => 'secondary',
        \App\Models\Reserva::ESTADO_PAGO_REPROGRAMADO => 'primary',
        \App\Models\Reserva::ESTADO_PAGO_REEMBOLSADO => 'secondary',
        \App\Models\Reserva::ESTADO_PAGO_FALLIDO => 'danger',
        default => 'secondary',
    };
@endphp

<span {{ $attributes->class(['badge', 'text-bg-'.$color]) }}>{{ $label }}</span>
