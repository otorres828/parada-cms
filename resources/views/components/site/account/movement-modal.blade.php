@props([
    'available' => 0
])

<div
    class="fixed inset-0 z-50 flex items-start md:items-center justify-center p-4 pt-20 pb-24 md:py-4 bg-background/80 backdrop-blur-sm overflow-y-auto"
    x-show="openModal"
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    @keydown.escape.window="toggleModal(false)"
    style="display:none;">

    <div
        class="w-full max-w-xl glass-panel rounded-2xl border border-white/10 p-6 space-y-6 shadow-2xl relative max-h-[calc(100dvh-7rem)] md:max-h-[85vh] overflow-y-auto"
        @click.away="toggleModal(false)"
        x-show="openModal"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95">

        {{ $slot }}

    </div>
</div>
