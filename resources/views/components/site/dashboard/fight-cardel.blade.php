{{--
|--------------------------------------------------------------------------
| FIGHT SCHEDULE / CARTELERA COMPONENT
|--------------------------------------------------------------------------
| Despliega el listado estructurado de combates vinculados al evento actual.
| Controla de manera reactiva la navegación por pestañas (Filtros de Estado)
| enviando las transiciones directas al backend a través de Livewire (`cambiarTab`).
| Cruza en caliente la información de cada pelea con el histórico precargado (Eager Loading)
| del usuario para reflejar indicadores gráficos sobre las apuestas en marcha.
|
| Reusable UI Components:
|   - <x-site.dashboard.match-row />     -> Fila modular para el despliegue de datos del combate y estados de juego.
|   - <x-layout.loader.fullpage />       -> Overlay translúcido de carga rápida durante la conmutación de pestañas.
|   - <x-site.geral.title-section />             -> Title para mostrar el titulo de la sección.
|--------------------------------------------------------------------------
--}}
@props(['peleasCartelera', 'tabFiltro', 'eventoEnVivo'])

<div {{ $attributes->merge(['class' => 'glass-panel rounded-3xl p-6 border border-white/5 bg-surface-container-low/30']) }}>
    {{-- Encabezado y Filtros Reactivos --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">

        <x-site.geral.title-section title="Cartelera de Peleas" />

        <div class="flex bg-surface-container rounded-xl p-1 self-start sm:self-auto border border-white/5">

            @if ($eventoEnVivo && !$eventoEnVivo->isComming())
                <button wire:click="cambiarTab('en_curso')" @class([
                    'px-4 py-2 text-xs font-black rounded-lg transition-all',
                    'bg-primary text-black shadow-md' => $tabFiltro === 'en_curso',
                    'text-on-surface-variant hover:text-white' => $tabFiltro !== 'en_curso',
                ])>En curso</button>
            @endif

            @if (!$eventoEnVivo->isTipoEquipo())

                <button wire:click="cambiarTab('proximos')" @class([
                    'px-4 py-2 text-xs font-black rounded-lg transition-all',
                    'bg-primary text-black shadow-md' => $tabFiltro === 'proximos',
                    'text-on-surface-variant hover:text-white' => $tabFiltro !== 'proximos',
                ])>Próximos</button>

            @endif

            @if ($eventoEnVivo && !$eventoEnVivo->isComming())
                <button wire:click="cambiarTab('finalizados')" @class([
                    'px-4 py-2 text-xs font-black rounded-lg transition-all',
                    'bg-primary text-black shadow-md' => $tabFiltro === 'finalizados',
                    'text-on-surface-variant hover:text-white' => $tabFiltro !== 'finalizados',
                ])>Finalizados</button>
            @endif

            @if ($eventoEnVivo && $eventoEnVivo->isTipoEquipo())
                <button wire:click="cambiarTab('equipos')" @class([
                    'px-4 py-2 text-xs font-black rounded-lg transition-all',
                    'bg-primary text-black shadow-md' => $tabFiltro === 'equipos',
                    'text-on-surface-variant hover:text-white' => $tabFiltro !== 'equipos',
                ])>Compromiso</button>
            @endif
        </div>
    </div>

    {{-- Contenedor de Listado de Peleas --}}
    <div class="relative w-full">

        @if ($tabFiltro === 'equipos' && $eventoEnVivo && $eventoEnVivo->isTipoEquipo())
            @php
                $metricasEq = $eventoEnVivo->getMetricasEquipos();
            @endphp
            <div class="p-6 rounded-2xl bg-surface-container border border-white/10 space-y-4">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-widest text-primary">Enfrentamiento Oficial por Compromiso</span>
                    <span class="text-xs text-on-surface-variant">Estatus: <strong>{{ $eventoEnVivo->isComming() ? 'Apuestas Abiertas (Pre-Evento)' : 'En Curso / En Proceso' }}</strong></span>
                </div>

                <div class="grid grid-cols-2 gap-4 text-center">
                    <div class="p-4 rounded-xl bg-red-950/40 border border-red-500/30">
                        <div class="text-xl font-black text-red-400">{{ $eventoEnVivo->equipo_a }}</div>
                        <div class="text-xs text-slate-300 mt-1">Apostado: ${{ number_format($metricasEq['total_equipo_a'], 2) }}</div>
                    </div>
                    <div class="p-4 rounded-xl bg-emerald-950/40 border border-emerald-500/30">
                        <div class="text-xl font-black text-emerald-400">{{ $eventoEnVivo->equipo_b }}</div>
                        <div class="text-xs text-slate-300 mt-1">Apostado: ${{ number_format($metricasEq['total_equipo_b'], 2) }}</div>
                    </div>
                </div>

                @if ($eventoEnVivo->isComming())
                    <div class="p-3 bg-amber-500/10 border border-amber-500/20 rounded-xl text-xs text-amber-300 text-center font-bold">
                        ¡Apuesta al ganador global del torneo de compromisos desde el panel derecho de apuestas!
                    </div>
                @elseif ($eventoEnVivo->ganador_equipo > 0)
                    <div class="p-3 bg-primary/10 border border-primary/20 rounded-xl text-sm text-primary text-center font-bold">
                        🏆 Compromiso Ganador del Evento: {{ $eventoEnVivo->ganador_equipo == 1 ? $eventoEnVivo->equipo_a : $eventoEnVivo->equipo_b }}
                    </div>
                @endif
            </div>
        @else
            <div class="space-y-3 max-h-[480px] overflow-y-auto pr-2 custom-scrollbar relative">
                @forelse($peleasCartelera as $pelea)

                    <x-site.dashboard.match-row :pelea="$pelea"  />

                @empty
                    <div class="text-center py-8 text-on-surface-variant text-sm border border-dashed border-white/5 rounded-2xl bg-surface-container-lowest/20">
                        No se encontraron combates en esta categoría.
                    </div>
                @endforelse
            </div>
        @endif

        {{-- Loader nativo del flujo Livewire posicionado de forma absoluta sobre todo el bloque --}}
        <x-layout.loader.fullpage wire:loading.delay.short wire:target="cambiarTab" />

    </div>

</div>
