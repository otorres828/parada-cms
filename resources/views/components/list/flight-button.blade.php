@props([
    'route',
    ])

<a  wire:navigate href="{{ $route ?? '#' }}"  class="btn btn-outline-secondary">

   <i class="bi bi-trophy-fill"></i>

</a>
