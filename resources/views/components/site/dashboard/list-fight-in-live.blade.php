@props(['misApuestasActivas','isDerby' => true])

@if ($misApuestasActivas->isNotEmpty())
    <div class="mt-6 pt-4 border-t border-white/10">
        <h5 class="text-xs font-black uppercase tracking-widest text-primary mb-3 flex items-center justify-between">
            <span>Mis Apuestas</span>
            <span
                class="bg-primary/20 text-primary px-2 py-0.5 rounded text-[10px]">{{ $misApuestasActivas->count() }}</span>
        </h5>

        <div class="space-y-2 max-h-48 overflow-y-auto pr-1 custom-scrollbar">
            @foreach ($misApuestasActivas as $apuestaItem)
                <div
                    class="p-3 rounded-xl bg-surface-container/60 border border-white/5 text-xs flex items-center justify-between">
                    <div>
                        <div class="font-bold text-white">
                            @if ($isDerby)
                            Pelea #{{ $apuestaItem->pelea_id }} -
                            {{ $apuestaItem->tipo_gallo == 1 ? 'Rojo' : 'Verde' }} -
                            @else
                            {{ $apuestaItem->equipo_seleccionado == 1 ? 'Rojo': 'Verde' }}
                            @endif
                            {{ $apuestaItem->getTipoApuesta() }} ({{ $apuestaItem->getCantidadApostada() }})
                        </div>
                        <div class="text-[10px] text-slate-400">
                            Status:
                            @if ($apuestaItem->isCalzada())
                                <span class="text-{{  $apuestaItem->getColorStatus() }}-400 font-bold">{{ $apuestaItem->getStatus() }}</span>
                            @else
                                <span class="text-amber-400 font-bold">Pendiente (Buscando Match)</span>
                            @endif
                        </div>
                    </div>

                    @if (!$apuestaItem->isCalzada())
                        <button type="button" x-on:click="cancelarApuesta({{ $apuestaItem->id }})"
                            class="px-2.5 py-1 text-[10px] font-bold bg-red-500/20 hover:bg-red-500/40 text-red-300 border border-red-500/30 rounded-lg transition-colors">
                            Cancelar
                        </button>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
@endif
