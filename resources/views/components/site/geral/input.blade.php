@props([
    'type' => 'text',
    'icon' => null,
])

<div class="relative w-full">
    @if($icon)
        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-on-surface-variant material-symbols-outlined">
            {{ $icon }}
        </span>
    @endif

    <input
        type="{{ $type }}"
        {{ $attributes->merge([
            'class' => 'w-full bg-surface-container-lowest border border-white/5 rounded-xl px-4 py-3 text-label-md text-on-surface focus:ring-1 focus:ring-primary outline-none' . ($icon ? ' pl-11' : '')
        ]) }}
    />
</div>
