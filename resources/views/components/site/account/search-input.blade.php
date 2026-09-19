@props([
    'model',
    'placeholder' => 'Buscar...'
])

<div class="relative w-full sm:w-64">
    <span
        class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-on-surface-variant text-md">
        search
    </span>

    <input
        wire:model.live.debounce.300ms="{{ $model }}"
        class="bg-surface-container-low border border-white/5 rounded-xl pl-12 pr-4 py-3.5 w-full text-label-md text-on-surface focus:ring-1 focus:ring-primary transition-all outline-none h-[52px]"
        placeholder="{{ $placeholder }}"
        type="text" />
</div>
