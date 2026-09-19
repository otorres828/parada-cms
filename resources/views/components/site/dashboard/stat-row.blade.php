@props(['icon', 'iconColor' => 'primary', 'label', 'pulse' => false])

<div class="flex items-center justify-between p-3.5 bg-surface-container rounded-xl border border-white/5 shadow-sm">
    <div class="flex items-center gap-3">
        <span @class([
            'material-symbols-outlined',
            'text-primary' => $iconColor === 'primary',
            'text-green-400' => $iconColor === 'success',
            'animate-pulse' => $pulse
        ])>{{ $icon }}</span>
        <span class="text-xs font-bold text-on-surface-variant">{{ $label }}</span>
    </div>
    <span class="font-bold text-sm text-white tracking-tight">
        {{ $slot }}
    </span>
</div>
