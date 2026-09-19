@props(['eventoEnVivo', 'peleaActiva'])

@php
    $embedUrl = $eventoEnVivo->link_streaming ?? '';
    if (str_contains($embedUrl, 'vimeo.com/')) {
        $path = parse_url($embedUrl, PHP_URL_PATH);
        $videoId = ltrim($path, '/');
        $embedUrl = "https://player.vimeo.com/video/{$videoId}?autoplay=1&title=0&byline=0&portrait=0";
    } elseif (str_contains($embedUrl, 'youtube.com/') || str_contains($embedUrl, 'youtu.be/')) {
        $videoId = '';
        if (str_contains($embedUrl, 'youtube.com/watch')) {
            parse_str(parse_url($embedUrl, PHP_URL_QUERY), $queryParams);
            $videoId = $queryParams['v'] ?? '';
        } elseif (str_contains($embedUrl, 'youtu.be/')) {
            $path = parse_url($embedUrl, PHP_URL_PATH);
            $videoId = ltrim($path, '/');
        }
        if ($videoId) {
            $embedUrl = "https://www.youtube.com/embed/{$videoId}?autoplay=1&rel=0";
        }
    }
@endphp

<div x-data="{ isPlaying: false }"
    class="relative -mx-4 w-[calc(100%+2rem)] aspect-[4/3] glass-panel rounded-3xl overflow-hidden border-2 border-primary/30 shadow-[0_0_30px_rgba(255,182,139,0.1)] bg-black md:mx-0 md:w-full md:aspect-video">
    <template x-if="isPlaying">
        <iframe class="absolute inset-0 w-full h-full z-10" src="{{ $embedUrl }}"
            title="Streaming de Arena Oficial" frameborder="0"
            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
            referrerpolicy="strict-origin-when-cross-origin"
            allowfullscreen>
        </iframe>
    </template>

    <div class="absolute inset-0 z-0" x-show="!isPlaying">
        <div class="w-full h-full bg-cover bg-center opacity-40 blur-xs"
            style="background-image: url('{{ $eventoEnVivo->getBanner() }}')">
        </div>
    </div>

    @if ($eventoEnVivo->isOnLive())
        <div class="absolute inset-0 bg-black/30 group cursor-pointer flex items-center justify-center transition-all hover:bg-black/20 z-20"
            x-show="!isPlaying" @click="isPlaying = true">
            <div
                class="w-20 h-20 bg-primary-container/20 backdrop-blur-xl rounded-full flex items-center justify-center border border-primary/50 transition-transform hover:scale-110 shadow-2xl">
                <span class="material-symbols-outlined text-primary text-5xl"
                    style="font-variation-settings: 'FILL' 1;">play_arrow</span>
            </div>
        </div>

        <div class="absolute bottom-0 left-0 right-0 p-6 bg-gradient-to-t from-black/90 via-black/50 to-transparent flex justify-between items-end z-20"
            x-show="!isPlaying">
            <div class="flex items-center gap-4">
                <div class="animate-pulse w-3 h-3 bg-primary rounded-full shadow-[0_0_10px_#ff9800]"></div>
                <div>
                    <h3 class="font-headline-md text-base md:text-headline-md text-white leading-snug md:leading-none">
                        {{ $peleaActiva ? "Pelea #{$peleaActiva->id}: {$peleaActiva->partido_rojo} vs {$peleaActiva->partido_verde}" : 'Preparando Siguiente Combate' }}
                    </h3>
                    <p class="text-[10px] text-on-surface-variant mt-1 uppercase tracking-widest font-black">
                        Arena Principal • En Vivo</p>
                </div>
            </div>
        </div>
    @endif

    

</div>
