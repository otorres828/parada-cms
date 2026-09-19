@props(['pelea'])

<div @class([
    'group flex flex-col md:flex-row items-center gap-4 p-4 rounded-2xl border transition-all',
    'border-primary/30 bg-primary/5 shadow-md shadow-primary/5' => $pelea->isPeleaActiva(),
    'border-white/5 hover:bg-white/5' => !$pelea->isPeleaActiva()
])>
    {{-- Número de Pelea Adaptado al Formato del Seeder --}}
    <div class="flex-shrink-0 w-12 text-center">
        <span class="text-on-surface-variant font-black text-[9px] uppercase tracking-wider block">Pelea</span>
        <p class="text-2xl font-black text-white leading-none mt-0.5">
            {{ $pelea->id }}
        </p>
    </div>

    <div class="flex-1 grid grid-cols-1 md:grid-cols-2 gap-4 items-center w-full">
        {{-- Bloque de Enfrentamiento (Meron vs Wala) --}}
        <div class="flex items-center justify-between gap-2">
            <div class="flex flex-col items-end flex-1 min-w-0">
                <span class="text-white font-bold text-sm  uppercase">{{ $pelea->partido_rojo }}</span>
                <span class="bg-red-500/10 text-red-400 text-[9px] px-2 py-0.5 rounded-md font-black uppercase mt-0.5 border border-red-500/20">Rojo</span>
            </div>
            <div class="flex flex-col items-center px-2">
                <span class="text-on-surface-variant font-black italic text-sm">VS</span>
                <span class="text-[9px] text-on-surface-variant font-bold mt-0.5">Anillo {{ $pelea->anillo }}</span>
            </div>
            <div class="flex flex-col items-start flex-1 min-w-0">
                <span class="text-white font-bold text-sm uppercase">{{ $pelea->partido_verde }}</span>
                <span class="bg-green-500/10 text-green-400 text-[9px] px-2 py-0.5 rounded-md font-black uppercase mt-0.5 border border-green-500/20">Verde</span>
            </div>
        </div>

        {{-- Bloque de Estado, Veredicto y Tu Apuesta Personal --}}
        <div class="flex items-center justify-between gap-3 w-full">

            {{-- Caja Estándar del Veredicto Oficial --}}
            <div class="flex-1 px-4 py-2 bg-surface-container-lowest rounded-xl border border-white/5 min-h-[46px] flex flex-col justify-center">
                <span class="text-[9px] text-on-surface-variant uppercase font-black tracking-widest block">Ganador</span>
                <span class="text-white font-bold text-xs flex items-center gap-1.5 mt-0.5">
                    @if($pelea->isPeleaFinalizada())
                        <span class="material-symbols-outlined text-primary text-sm">emoji_events</span>
                        <span class="uppercase">
                            {{ $pelea->getGanador() }} {{ $pelea->es_pelea_equipo ? '(Pelea de compromiso)' : '' }}
                        </span>
                    @elseif($pelea->isPeleaActivaOrAnillo())
                        <span class="w-2 h-2 bg-primary rounded-full animate-ping mr-1"></span>
                        <span class="text-primary uppercase tracking-wider text-[11px]">En Curso {{ $pelea->es_pelea_equipo ? '(Pelea de compromiso)' : '' }}</span>
                    @else
                        <span class="text-on-surface-variant">En Espera {{ $pelea->es_pelea_equipo ? '(Pelea de compromiso)' : '' }}</span>
                    @endif
                </span>
            </div>
        </div>
    </div>
</div>
