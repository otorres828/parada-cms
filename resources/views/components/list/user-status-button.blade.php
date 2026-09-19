@props(['status'])


<button type="button" class="btn btn-outline-secondary" {{ $attributes }}>

   @switch($status)
      @case(2)
      @case(3)
         {{-- Bloqueado --}}
         <i class="bi bi-play-fill"></i>
      @break
      @case(1)
         {{-- Activo --}}
         <i class="bi bi-pause-fill"></i>
      @break

   @endswitch

</button>
