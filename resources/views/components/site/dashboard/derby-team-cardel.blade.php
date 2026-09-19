@props(['peleasCartelera','tabFiltro','eventoEnVivo'])

@if ($tabFiltro === 'equipos' && $eventoEnVivo)
    @php
        $metricasEq = $eventoEnVivo->getMetricasEquipos();
    @endphp
    <div class="p-6 rounded-2xl bg-surface-container border border-white/10 space-y-4">
        <div class="flex items-center justify-between">
            <span class="text-xs font-bold uppercase tracking-widest text-primary">Enfrentamiento Oficial por
                Compromiso</span>
            {{-- <span class="text-xs text-on-surface-variant">Estatus:
                <strong>{{ $eventoEnVivo->isComming() ? 'Apuestas Abiertas (Pre-Evento)' : 'En Curso / En Proceso' }}</strong></span> --}}
        </div>

        <div class="grid grid-cols-2 gap-4 text-center">
            <div class="p-4 rounded-xl bg-red-950/40 border border-red-500/30">
                <div class="text-xl font-black text-red-400">{{ $eventoEnVivo->equipo_a }}</div>
                <div class="text-xs text-slate-300 mt-1">Apostado:
                    ${{ number_format($metricasEq['total_equipo_a'], 2) }}</div>
            </div>
            <div class="p-4 rounded-xl bg-emerald-950/40 border border-emerald-500/30">
                <div class="text-xl font-black text-green-400">{{ $eventoEnVivo->equipo_b }}</div>
                <div class="text-xs text-slate-300 mt-1">Apostado:
                    ${{ number_format($metricasEq['total_equipo_b'], 2) }}</div>
            </div>
        </div>

        @if ($eventoEnVivo->isComming())
            <div
                class="p-3 bg-amber-500/10 border border-amber-500/20 rounded-xl text-xs text-amber-300 text-center font-bold">
                ¡Apuesta al ganador antes de que inicie el evento! Las apuestas por compromiso se cerrarán cuando
                inicie el evento </div>
        @else
            @php
                $apuestasAlEquipo = $this->apuestasEquipoUsuario ?? collect();
            @endphp

            <x-site.dashboard.list-fight-in-live :misApuestasActivas="$apuestasAlEquipo" :isDerby="false" />

        @endif
    </div>
@else
    <div class="space-y-3 max-h-[480px] overflow-y-auto pr-2 custom-scrollbar relative">
        @forelse($peleasCartelera as $pelea)

            <x-site.dashboard.match-row :pelea="$pelea" />

        @empty
            <div
                class="text-center py-8 text-on-surface-variant text-sm border border-dashed border-white/5 rounded-2xl bg-surface-container-lowest/20">
                No se encontraron combates en esta categoría.
            </div>
        @endforelse
    </div>
@endif
