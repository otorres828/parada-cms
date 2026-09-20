@props(['status'])

@php
    $value = is_bool($status) ? (string) (int) $status : (string) $status;
    [$label, $color] = match ($value) {
        '1' => ['Activo', 'success'],
        '2' => ['Inactivo', 'warning'],
        '3' => ['Finalizado', 'secondary'],
        default => [$value !== '' ? $value : 'Sin estado', 'secondary'],
    };
@endphp

<span {{ $attributes->class(['badge', 'text-bg-'.$color]) }}>{{ $label }}</span>
