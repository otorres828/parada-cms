@props([
    'variant' => 'primary', // primary, secondary, outline, success, danger
])

@php
    $baseClasses = "flex items-center justify-center gap-2 font-bold text-label-md rounded-xl transition-all active:scale-95";

    $variants = [
        'primary' => 'bg-primary text-on-primary shadow-lg shadow-primary/10 hover:opacity-90',
        'secondary' => 'border border-white/10 text-on-surface hover:bg-white/5',
        'outline' => 'border border-primary text-primary hover:bg-primary/5',
        'success' => 'border border-success bg-success/5 text-success',
        'danger' => 'border border-danger bg-danger/5 text-danger',
    ];

    $classes = $baseClasses . ' ' . ($variants[$variant] ?? $variants['primary']);
@endphp

<button {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</button>
