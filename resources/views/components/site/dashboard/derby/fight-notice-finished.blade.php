@props(['alertasPeleasFinalizadas'=>[]])
@if (count($alertasPeleasFinalizadas) > 0)
    <div class="order-2 space-y-2 md:order-none" aria-live="polite">
        @foreach ($alertasPeleasFinalizadas as $alertaFinalizada)
            <div wire:key="alerta-pelea-finalizada-{{ $alertaFinalizada['id'] }}" x-data="{ visible: true, timer: null }"
                x-init="timer = setTimeout(() => { visible = false;
                    $wire.cerrarAlertaPeleaFinalizada({{ $alertaFinalizada['id'] }}) }, 10000)" x-show="visible" x-transition.opacity.duration.300ms
                class="flex items-start gap-3 rounded-2xl border border-emerald-400/30 bg-emerald-400/10 px-4 py-3 text-emerald-100 shadow-lg shadow-emerald-950/10"
                role="alert">
                <span class="material-symbols-outlined mt-0.5 shrink-0 text-xl text-emerald-300">check_circle</span>
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-black text-emerald-200">Pelea finalizada</p>
                    <p class="mt-0.5 break-words text-xs leading-relaxed sm:text-sm">
                        Pelea #{{ $alertaFinalizada['id'] }}:
                        {{ $alertaFinalizada['partido_rojo'] }} vs {{ $alertaFinalizada['partido_verde'] }}.
                        Resultado: <strong class="text-md">{{ $alertaFinalizada['ganador'] }}</strong>.
                    </p>
                </div>
                <button type="button"
                    @click="clearTimeout(timer); visible = false; setTimeout(() => $wire.cerrarAlertaPeleaFinalizada({{ $alertaFinalizada['id'] }}), 200)"
                    class="grid h-7 w-7 shrink-0 place-items-center rounded-lg text-emerald-200/70 transition hover:bg-emerald-300/10 hover:text-emerald-100"
                    aria-label="Cerrar aviso de pelea finalizada">
                    <span class="material-symbols-outlined text-lg">close</span>
                </button>
            </div>
        @endforeach
    </div>
@endif
