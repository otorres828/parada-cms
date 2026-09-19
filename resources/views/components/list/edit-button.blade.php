@props(['route'])

<a wire:navigate href="{{ $route ?? '#' }}" class="btn btn-outline-secondary" title="Editar registro" aria-label="Editar registro">

   <i class="bi bi-pencil-fill"></i>

</a>