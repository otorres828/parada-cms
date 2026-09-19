@props(['route', 'target' => true])
<a href="{{ $route }}" @if($target) target="_blank" rel="noopener" @else wire:navigate @endif class="btn btn-outline-secondary" title="Ver detalle" aria-label="Ver detalle">
    <i class="bi bi-eye-fill" aria-hidden="true"></i>
</a>