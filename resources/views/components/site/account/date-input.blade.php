@props([
    'label',                 // El texto de la etiqueta superior: "Desde" o "Hasta"
    'ref',                   // La referencia para Alpine: "date_from" o "date_to"
    'key',                   // La clave única requerida por Livewire (wire:key)
    'placeholder' => 'dd-mm-yyyy'
])

<label wire:ignore wire:key="{{ $key }}" {{ $attributes->merge(['class' => 'flex items-center gap-2 px-4 py-2 bg-surface-container-high rounded-xl border border-white/5 hover:bg-white/5 focus-within:bg-white/5 transition-colors cursor-pointer w-full md:w-auto']) }}>
    <span class="material-symbols-outlined text-on-surface-variant">calendar_today</span>

    <div class="flex flex-col min-w-0 flex-1 md:min-w-[100px]">
        <span class="text-[10px] text-on-surface-variant uppercase font-bold tracking-wider leading-none mb-1">
            {{ $label }}
        </span>

        <input
            x-ref="{{ $ref }}"
            type="text"
            placeholder="{{ $placeholder }}"
            autocomplete="off"
            class="bg-transparent border-none p-0 text-label-md font-label-md text-on-surface focus:ring-0 cursor-pointer w-full"
        />
    </div>
</label>
