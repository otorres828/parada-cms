@props(['status'])

@php
    $value = is_bool($status) ? (string) (int) $status : (string) $status;
    [$label, $color] = match ($value) {
        '1' => ['Activo', 'success'],
        '0', '2' => ['Inactivo', 'warning'],
        'nuevo' => ['Nueva', 'info'],
        'pendiente' => ['Pendiente', 'warning'],
        'aprobado' => ['Aprobado', 'info'],
        'pagado' => ['Pagado', 'success'],
        'rechazado' => ['Rechazado', 'danger'],
        'fallido' => ['Fallido', 'danger'],
        'cancelado' => ['Cancelado', 'secondary'],
        'reembolsado' => ['Reembolsado', 'secondary'],
        default => [$value !== '' ? $value : 'Sin estado', 'secondary'],
    };
@endphp

<span {{ $attributes->class(['badge', 'text-bg-'.$color]) }}>{{ $label }}</span>
