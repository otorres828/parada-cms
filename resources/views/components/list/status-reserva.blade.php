@props(['status'])

@php
    $value = is_bool($status) ? (string) (int) $status : (string) $status;
    [$label, $color] = match ($value) {
        '1' => ['Nueva', 'info'],
        '2' => ['Pagado', 'success'],
        '3' => ['Pendiente', 'warning'],
        '4' => ['Cancelado', 'secondary'],
        '5' => ['Fallido', 'danger'],
        '6' => ['Reembolsado', 'secondary'],
        default => [$value !== '' ? $value : 'Sin estado', 'secondary'],
    };
@endphp

<span {{ $attributes->class(['badge', 'text-bg-'.$color]) }}>{{ $label }}</span>
