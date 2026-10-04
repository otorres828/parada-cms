@props(['route', 'disabled' => false, 'disabledReason' => 'Este registro no puede editarse.'])

@if ($disabled)

    <span class="d-inline-flex" title="{{ $disabledReason }}">
        <button type="button" class="btn btn-outline-secondary" disabled aria-label="{{ $disabledReason }}">
            <i class="bi bi-pencil-fill"></i>
        </button>
    </span>

@else

<a wire:navigate href="{{ $route ?? '#' }}" class="btn btn-outline-secondary" title="Editar registro" aria-label="Editar registro">

   <i class="bi bi-pencil-fill"></i>

</a>
@endif
