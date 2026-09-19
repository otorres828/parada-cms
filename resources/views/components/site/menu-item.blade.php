<a class="flex items-center gap-4 px-4 py-3 transition-all duration-300 rounded-xl
    {{ $isActive ? 'bg-primary-container text-on-primary-container font-bold' : 'text-on-surface-variant hover:text-on-surface hover:bg-surface-variant/50' }}"
   href="{{ $route }}" wire:navigate>

    <span class="material-symbols-outlined">{{ $icon }}</span>
    <span class="font-label-md text-label-md">{{ $label }}</span>

</a>
