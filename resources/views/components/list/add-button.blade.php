@props(['route'])

<a wire:navigate href="{{ $route ?? '#' }}" class="btn btn-primary">
    <i class="bi bi-plus-lg me-1"></i> {{ $slot }}
</a>