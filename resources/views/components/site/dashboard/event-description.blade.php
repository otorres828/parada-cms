@props(['description'])

@if (filled($description))
    <section
        {{ $attributes->merge(['class' => 'glass-panel w-full rounded-3xl border border-white/5 bg-surface-container-low/30 p-4 sm:p-6']) }}
        x-data="{ expanded: false }">
        <h2 id="event-description-title">
            <button type="button"
                class="flex w-full items-center gap-3 text-left"
                x-on:click="expanded = !expanded"
                x-bind:aria-expanded="expanded"
                aria-controls="event-description-content">
                <span class="material-symbols-outlined text-primary" aria-hidden="true">description</span>
                <span class="flex-1 text-sm font-black uppercase tracking-widest text-white">
                    Descripción del evento
                </span>
                <span
                    class="material-symbols-outlined text-on-surface-variant transition-transform duration-200"
                    x-bind:class="expanded && 'rotate-180'"
                    aria-hidden="true">expand_more</span>
            </button>
        </h2>

        <div id="event-description-content"
            x-cloak
            x-show="expanded"
            x-collapse>
            <p class="mt-3 whitespace-pre-line text-sm leading-relaxed text-on-surface-variant">
                {{ $description }}
            </p>
        </div>
    </section>
@endif
