@props([
    'status' => 'upcoming', // 'live' o 'upcoming'
    'image' => '',
    'category' => '',
    'title' => '',
    'eventID' => null,
    'fecha'=> null,
])

<div {{ $attributes->merge([
    'class' => 'snap-start glass-panel rounded-2xl overflow-hidden group cursor-pointer border transition-all ' .
    ($status === 'live' ? 'min-w-[320px] border-primary/20' : 'min-w-[300px] border-white/5')
]) }}>
    <a href="{{route('account.dashboard',['id'=>$eventID]) }}" wire:navigate>

        <div class="relative h-40 bg-surface-container-highest">
            <!-- Imagen con opacidad condicional si no está en vivo -->
            <div class="w-full h-full bg-cover bg-center transition-transform duration-500 group-hover:scale-110 {{ $status === 'upcoming' ? 'opacity-70 group-hover:opacity-100 transition-opacity' : '' }}"
                 style="background-image: url('{{ $image }}')">
            </div>

            <!-- Tag Superior Izquierdo Flotante -->
            @if($status === 'live')
                <div class="absolute top-3 left-3 bg-secondary-container text-white px-2 py-0.5 rounded text-[10px] font-bold flex items-center gap-1 uppercase tracking-widest animate-pulse">
                    <span class="w-1.5 h-1.5 bg-white rounded-full"></span> LIVE NOW
                </div>
            @else
                <div class="absolute top-3 left-3 bg-surface-container-high text-on-surface px-2 py-0.5 rounded text-[10px] font-bold flex items-center gap-1 uppercase tracking-widest">
                    {{ $fecha }}
                </div>
            @endif

            <div class="absolute inset-0 bg-gradient-to-t from-surface to-transparent"></div>

            <!-- Textos sobre la imagen -->
            <div class="absolute bottom-3 left-3 right-3 flex justify-between items-end">
                <div>
                    <p class="font-label-md text-[12px] {{ $status === 'live' ? 'text-primary' : 'text-on-surface-variant' }}">
                        {{ $category }}
                    </p>
                    <p class="font-headline-sm text-headline-sm text-white leading-tight">
                        {{ $title }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Sección de Acciones Inferior -->
        <div class="p-4">
            @if($status === 'live')
                <div class="flex justify-between items-center text-sm">
                    <span class="text-on-surface-variant flex items-center gap-1">
                        <span class="material-symbols-outlined text-[16px]">group</span>
                        En vivo
                    </span>
                    <span class="text-primary font-bold">Ver Streaming</span>
                </div>
            @else
                <x-site.geral.button type="button" variant="secondary" class="w-full py-2 text-sm font-semibold !h-auto">
                    Próximamente
                </x-site.geral.button>
            @endif
        </div>
    </a>

</div>
