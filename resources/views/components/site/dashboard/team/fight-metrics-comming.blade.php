{{--
|--------------------------------------------------------------------------
| LIVE FIGHT METRICS COMPONENT
|--------------------------------------------------------------------------
| Despliega el resumen analítico y los volúmenes transaccionales en vivo.
| Muestra una matriz dividida por gallo (Rojo/Verde) y sus posturas (Doy/Agarro)
| filtradas por modalidad/línea.
|--------------------------------------------------------------------------
--}}
@props(['eventoEnVivo', 'modalidades', 'usuariosEnVivo' => 0])

<div
    {{ $attributes->merge(['class' => 'glass-panel rounded-3xl p-6 border border-white/5 bg-gradient-to-b from-surface-container-low/40 to-transparent shadow-xl']) }}>

    {{-- Cabecera con indicador de latido --}}
    <div class="flex items-center justify-between mb-5">
        <h4 class="font-headline-md text-headline-md text-on-surface-variant">Totales jugando</h4>
        <span class="flex h-2 w-2 relative">
            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-primary opacity-75"></span>
            <span class="relative inline-flex rounded-full h-2 w-2 bg-primary"></span>
        </span>
    </div>

    {{-- Desglose Matricial por Modalidades --}}
    <div class="flex flex-col gap-4 mb-2">

        @foreach ($modalidades as $mod)
            <div class="grid grid-cols-2 gap-3">

                {{-- BLOQUE GALLO ROJO (TIPO GALLO 1) --}}

                <div class="w-full grid grid-cols-1 gap-1 text-center">

                    <div class="bg-surface-container-lowest/40 border border-white/5 rounded-xl p-2.5 flex flex-col items-center @if($mod->id != 1) mb-2 @endif">

                        <div class="p-1.5 rounded-lg ">
                            <span
                                class="text-red-400 block text-md font-bold  uppercase">{{ $mod->id != 1 ? 'Doy' : '' }}
                                {{ $mod->nombre }}</span>
                            <span class="text-white font-mono font-black text-xs">
                                ${{ number_format($eventoEnVivo->getVolumenPorModalidad(1, 1, $mod->id), 2) }}
                            </span>
                        </div>

                    </div>


                    @if ($mod->id != 1)
                        <div class="bg-surface-container-lowest/40 border border-white/5 rounded-xl p-2.5 flex flex-col items-center">
                            <div class="p-1.5 rounded-lg">
                                <span class="text-red-400 block text-md font-bold  uppercase">Agarro
                                    {{ $mod->nombre }}</span>
                                <span class="text-white font-mono font-black text-xs">
                                    ${{ number_format($eventoEnVivo->getVolumenPorModalidad(1, 2, $mod->id), 2) }}
                                </span>
                            </div>
                        </div>
                    @endif

                </div>

                {{-- BLOQUE GALLO VERDE (TIPO GALLO 2) --}}

                <div class="w-full grid grid-cols-1 gap-1 text-center">

                    <div
                        class="bg-surface-container-lowest/40 border border-white/5 rounded-xl p-2.5 flex flex-col items-center @if($mod->id != 1) mb-2 @endif">

                        <div class=" p-1.5 rounded-lg">
                            <span
                                class="text-green-400 block text-md font-bold  uppercase">{{ $mod->id != 1 ? 'Agarro' : '' }}
                                {{ $mod->nombre }}</span>
                            <span class="text-white font-mono font-black text-xs">
                                ${{ number_format($eventoEnVivo->getVolumenPorModalidad(2, 2, $mod->id), 2) }}
                            </span>
                        </div>

                    </div>

                    @if ($mod->id != 1)
                        <div
                            class="bg-surface-container-lowest/40 border border-white/5 rounded-xl p-2.5 flex flex-col items-center">

                            <div class=" p-1.5 rounded-lg">
                                <span class="text-green-400 block text-md font-bold  uppercase">Doy
                                    {{ $mod->nombre }}</span>
                                <span class="text-white font-mono font-black text-xs">
                                    ${{ number_format($eventoEnVivo->getVolumenPorModalidad(2, 1, $mod->id), 2) }}
                                </span>
                            </div>

                        </div>
                    @endif

                </div>

            </div>
        @endforeach
    </div>

</div>

{{-- Resumen y Totales Consolidados al Pie --}}
<div class="border-t border-white/5 pt-4 space-y-3">


    <div class="flex justify-between items-center text-xs pt-1">
        <span class="text-on-surface-variant font-medium flex items-center gap-1.5">
            <span class="material-symbols-outlined text-sm text-white/60">group</span>
            Jugadores en Vivo:
        </span>
        <span class="text-primary font-bold bg-primary/10 px-2 py-0.5 rounded text-[11px]">
            {{ number_format($usuariosEnVivo) }} Jugadores
        </span>
    </div>
</div>
</div>
