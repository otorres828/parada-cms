@props(['status'])


<button type="button" class="btn btn-outline-secondary" {{ $attributes }}>

   @switch($status)
      @case(1)
         {{-- Activo --}}
         <i class="bi bi-pause-fill"></i>
      @break
     @default
         {{-- Inactivo --}}
         <i class="bi bi-play-fill"></i>
   @endswitch

</button>