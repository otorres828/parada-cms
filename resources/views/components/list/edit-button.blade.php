@props(['route', 'disabled' => false, 'disabledReason' => 'Este registro no puede editarse.'])

@if ($disabled)

    <button type="button" class="btn btn-outline-secondary" disabled
        title="{{ $disabledReason }}" aria-label="{{ $disabledReason }}">

        <i class="bi bi-pencil-fill"></i>

    </button>

@else

    <a wire:navigate href="{{ $route ?? '#' }}" class="btn btn-outline-secondary"
        title="Editar registro" aria-label="Editar registro">

        <i class="bi bi-pencil-fill"></i>

    </a>

@endif
