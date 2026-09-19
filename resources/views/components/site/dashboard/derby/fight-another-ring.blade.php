@props([
    'peleasOtroAnillo' => collect(),
])
@if ($peleasOtroAnillo->isNotEmpty())
    <div class="order-2 md:order-none space-y-2" role="status" aria-live="polite">
        @foreach ($peleasOtroAnillo as $key => $peleaOtroAnillo)
            <div wire:key="aviso-otro-anillo-{{ $peleaOtroAnillo->id }}"
                class="rounded-2xl border border-sky-400/30 bg-sky-400/10 px-4 py-3 text-sm text-sky-100">
                <span class="font-black">Aviso: {{ $key + 1 }}</span>
                Ronda {{ $peleaOtroAnillo->rounds ?? '—' }} - Pelea #{{ $peleaOtroAnillo->id }} se ha pasado al anillo
                {{ $peleaOtroAnillo->anillo }}. En un momento se indicará el ganador, pero puedes disfrutar la actual
                pelea.
            </div>
        @endforeach
    </div>
@endif
