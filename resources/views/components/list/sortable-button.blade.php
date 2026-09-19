@props(['sortColumn', 'sortDirection', 'column'])

<button type="button" class="bg-transparent border-0 p-0 focus:outline-none focus:ring-0 text-black" wire:click="sortBy('{{ $column }}')">
   @if ($sortColumn === $column)
      @if ($sortDirection === 'asc')
         <i class="bi bi-arrow-down"></i>
      @else
         <i class="bi bi-arrow-up"></i>
      @endif
   @else
      <i class="bi bi-arrow-down-up"></i>
   @endif
</button>
