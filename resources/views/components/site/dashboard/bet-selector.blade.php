{{--
|--------------------------------------------------------------------------
| BETTING SELECTOR
|--------------------------------------------------------------------------
| Controla la interfaz de selección de modalidades y cuotas de apuesta, con reactividad local
|
| Reusable UI Components:
|    - <x-site.geral.button /> -> Selector de modalidades y cuotas de apuesta, con reactividad local.
|--------------------------------------------------------------------------
--}}
@props(['modalidades'])
<div wire:ignore.self>
     {{-- Contenedor de Botones de Cuotas Dinámicas --}}
     <div class="flex flex-col gap-2 mb-6">
         @foreach ($modalidades as $mod)
             <div class="grid grid-cols-2 gap-4">

                 {{-- COLUMNA ROJO (GALLO 1) --}}
                 <div class="grid grid-cols-1 gap-3">
                     <!-- Rojo DOY -->
                     <button type="button"
                         @click="seleccionarCuota(1, 1, {{ $mod->id }}, {{ $mod->porcentaje }}, 'doy')"
                         :class="tipoGallo === 1 && modalidadApuesta === 1 && modalidadId === {{ $mod->id }} ?
                             'ring-2 ring-red-500 border-red-500 scale-[1.02]' : 'opacity-70 hover:opacity-100'"
                         class="wala-gradient glow-crimson p-4 rounded-2xl flex flex-col items-center gap-1 transition-all active:scale-95 group border border-transparent">
                         <span class="text-white font-mono font-black text-xl">{{ $mod->id != 1 ? 'Doy' : '' }}
                             {{ $mod->nombre }}</span>
                     </button>

                     @if ($mod->id != 1)
                         <!-- Rojo AGARRO -->
                         <button type="button"
                             @click="seleccionarCuota(1, 2, {{ $mod->id }}, {{ $mod->porcentaje }}, 'agarro')"
                             :class="tipoGallo === 1 && modalidadApuesta === 2 && modalidadId === {{ $mod->id }} ?
                                 'ring-2 ring-red-500 border-red-500 scale-[1.02]' : 'opacity-70 hover:opacity-100'"
                             class="wala-gradient glow-crimson px-2 md:px-4 py-4 rounded-2xl flex flex-col items-center gap-1 transition-all active:scale-95 group border border-transparent">
                             <span class="text-white font-mono font-black text-xl">Agarro {{ $mod->nombre }}</span>
                         </button>
                     @endif
                 </div>

                 {{-- COLUMNA VERDE (GALLO 2) --}}
                 <div class="grid grid-cols-1 gap-3">

                     <!-- Verde AGARRO -->
                     <button type="button"
                         @click="seleccionarCuota(2, 2, {{ $mod->id }}, {{ $mod->porcentaje }}, 'agarro')"
                         :class="tipoGallo === 2 && modalidadApuesta === 2 && modalidadId === {{ $mod->id }} ?
                             'ring-2 ring-primary border-primary scale-[1.02]' : 'opacity-70 hover:opacity-100'"
                         class="meron-gradient glow-crimson px-2 md:px-4 py-4 rounded-2xl flex flex-col items-center gap-1 transition-all active:scale-95 group border border-transparent">
                         <span class="text-white font-mono font-black text-xl">{{ $mod->id != 1 ? 'Agarro' : '' }}
                             {{ $mod->nombre }}</span>
                     </button>

                     @if ($mod->id != 1)
                         <!-- Verde DOY -->
                         <button type="button"
                             @click="seleccionarCuota(2, 1, {{ $mod->id }}, {{ $mod->porcentaje }}, 'doy')"
                             :class="tipoGallo === 2 && modalidadApuesta === 1 && modalidadId === {{ $mod->id }} ?
                                 'ring-2 ring-primary border-primary scale-[1.02]' : 'opacity-70 hover:opacity-100'"
                             class="meron-gradient glow-crimson p-4 rounded-2xl flex flex-col items-center gap-1 transition-all active:scale-95 group border border-transparent">
                             <span class="text-white font-mono font-black text-xl">Doy {{ $mod->nombre }}</span>
                         </button>
                     @endif

                 </div>

             </div>
         @endforeach
     </div>

     {{-- Formulario Dinámico --}}
     <div class="space-y-4">
         <div class="flex justify-between text-xs font-bold">
             <span class="text-on-surface-variant">Monto a Apostar</span>
             <span class="text-primary hover:underline cursor-pointer" @click="betAmount = balanceMax">
                 Disponible: $ MXN <span x-text="balanceMax.toFixed(2)"></span>
             </span>
         </div>

         <div class="relative rounded-xl overflow-hidden focus-within:ring-1 focus-within:ring-primary">
             <input x-model.number="betAmount" @input="if(betAmount > balanceMax) betAmount = balanceMax"
                 class="w-full bg-surface-container border border-white/5 focus:ring-0 rounded-xl py-3 px-4 text-white font-black text-xl outline-none transition-all pr-14"
                 type="number" min="1">
             <span
                 class="absolute right-4 top-1/2 -translate-y-1/2 text-on-surface-variant font-black text-xs">MXN</span>
         </div>

         <div class="grid grid-cols-4 gap-2">
             <button type="button" @click="addAmount(10)"
                 class="bg-surface-container border border-white/5 hover:bg-surface-container-high py-2 rounded-lg text-[11px] font-black transition-colors">+$10</button>
             <button type="button" @click="addAmount(50)"
                 class="bg-surface-container border border-white/5 hover:bg-surface-container-high py-2 rounded-lg text-[11px] font-black transition-colors">+$50</button>
             <button type="button" @click="addAmount(100)"
                 class="bg-surface-container border border-white/5 hover:bg-surface-container-high py-2 rounded-lg text-[11px] font-black transition-colors">+$100</button>
             <button type="button" @click="betAmount = balanceMax"
                 class="bg-surface-container border border-white/5 hover:bg-surface-container-high py-2 rounded-lg text-[11px] font-black text-primary transition-colors">MAX</button>
         </div>

         <x-site.geral.button type="button" variant="primary" @click="confirmarApuesta()"
             class="w-full py-4 uppercase tracking-widest font-black shadow-xl shadow-primary/10">
             Confirmar Apuesta
         </x-site.geral.button>
     </div>

 </div>
