@props([
    'id',
    'title' => '',
    'icon' => 'swap_horizontal_circle',
    'wireTarget' => null
])

<div
    x-cloak
    x-show="openModal"
    class="fixed inset-0 z-50 flex items-start md:items-center justify-center p-4 pt-20 pb-24 md:py-4 bg-background/80 backdrop-blur-sm overflow-y-auto"
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    @keydown.escape.window="closeModalForm()"
    style="display: none;"
>
    <div
        class="relative flex flex-col w-full max-w-xl p-6 space-y-6 border shadow-2xl rounded-2xl border-white/10 glass-panel max-h-[calc(100dvh-7rem)] md:max-h-[85vh] overflow-y-auto"
        @click.stop
        x-show="openModal"
    >
        {{-- Loader de Livewire opcional --}}
        @if($wireTarget)
            <div wire:loading wire:target="{{ $wireTarget }}" class="absolute inset-0 z-50 flex items-center justify-center rounded-2xl bg-background/60 backdrop-blur-[2px]">
                <div class="flex flex-col items-center justify-center">
                    <div class="w-12 h-12 border-4 border-primary border-t-transparent rounded-full animate-spin"></div>
                    <p class="mt-4 font-bold text-label-md text-primary tracking-wide">Procesando...</p>
                </div>
            </div>
        @endif

        {{-- Cabecera --}}
        <div class="flex items-center justify-between pb-4 border-b border-white/5">
            <div class="flex items-center gap-3">
                <span class="material-symbols-outlined text-primary text-3xl">{{ $icon }}</span>
                <h3 class="font-headline-md text-headline-md text-on-surface">{{ $title }}</h3>
            </div>
            <button type="button" @click="closeModalForm()"
                class="p-1 transition-colors rounded-lg text-on-surface-variant hover:text-on-surface hover:bg-white/5">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>

        {{-- Contenido --}}
        <div class="flex-1">
            {{ $slot }}
        </div>
    </div>
</div>
