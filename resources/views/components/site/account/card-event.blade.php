{{--
|--------------------------------------------------------------------------
| HISTORIC EVENT CARD COMPONENT
|--------------------------------------------------------------------------
| Renderiza de forma individual la tarjeta contenedora para cada evento finalizado.
| Presenta el banner dinámico del evento aplicando transiciones de escala mediante
| clases del grupo hover, acopla de manera reactiva el conteo total de peleas cargadas
| en la relación estructural del modelo, e implementa navegación nativa optimizada
| de Livewire (`wire:navigate`) para acceder al reporte detallado de resultados.
|--------------------------------------------------------------------------
--}}
@props(['evento'])
<div
    class="glass-panel rounded-2xl overflow-hidden glow-border transition-all duration-300 flex flex-col group hover:-translate-y-1">

    <div class="relative h-48 overflow-hidden">
        <img class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
            alt="{{ $evento->nombre }}"
            src="{{ $evento->getBanner() ?: 'https://lh3.googleusercontent.com/aida-public/AB6AXuBMymiyAsvjlT4vPlRPeYnLcZikGfdOXnikQem4Dvb2Pl-Uiofsljb-a97bdfoI6lW-2F91dKNVUFw1RPlph71JGHyCUyHgfkMzmjRCnF97Ame1yg5YJYQ8Y1a3w-2AmkapkbsDCD3oymjtYuLfCWLbwbNDKTfgvuEXcr2WQfWvuZa_GzFIEejh3Iiv4sZZ2GQn4P_SmBkZsErvHOUconrpI4xVOXw2ynE5_iZRmetWLTSySoES3LxKt3gV6PNqFJApPj43IRPidg' }}" />

        <div class="absolute inset-0 bg-gradient-to-t from-surface-container-lowest to-transparent opacity-60"></div>

        <div class="absolute top-4 left-4">
            <span
                class="px-3 py-1 bg-primary/20 backdrop-blur-md border border-primary/30 text-primary text-[11px] font-bold uppercase tracking-wider rounded-full">
                Finalizado
            </span>
        </div>
    </div>

    <div class="p-5 flex flex-col flex-1">
        <div class="flex justify-between items-start mb-2">
            <h3 class="font-headline-md text-[18px] text-on-surface line-clamp-1">
                {{ $evento->nombre }}
            </h3>
        </div>

        <div class="flex items-center gap-4 text-on-surface-variant text-[13px] mb-6">
            <div class="flex items-center gap-1">
                <span class="material-symbols-outlined text-[16px]">schedule</span>
                {{ $evento->fecha_evento ? $evento->fecha_formatted : 'S/F' }}
            </div>
            <div class="flex items-center gap-1">
                <span class="material-symbols-outlined text-[16px]">sports_martial_arts</span>
                {{ $evento->peleas()->count() }} Peleas
            </div>
        </div>

        <div class="mt-auto">
            <a href="{{ route('account.events.detail', ['evento' => $evento->id]) }}" wire:navigate
                class="block w-full text-center py-3 bg-surface-variant/20 hover:bg-primary hover:text-on-primary border border-white/5 hover:border-primary transition-all duration-300 font-label-md text-label-md rounded-xl">
                Ver Resultados
            </a>
        </div>
    </div>

</div>
