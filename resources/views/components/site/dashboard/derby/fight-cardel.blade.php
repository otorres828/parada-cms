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

<div
    {{ $attributes->merge(['class' => 'glass-panel w-full min-w-0 overflow-hidden rounded-3xl border border-white/5 bg-surface-container-low/30 p-4 sm:p-6']) }}>

    {{-- Encabezado y Filtros Reactivos --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">

        <div class="flex w-full flex-col items-start gap-3 sm:w-auto sm:flex-row sm:items-center">
            <x-site.geral.title-section title="Cartelera de Peleas" />
            <livewire:shared.event-statistics :evento="$eventoEnVivo" :key="'dashboard-statistics-' . $eventoEnVivo->id" />
        </div>

        <div class="flex w-full min-w-0 max-w-full self-start overflow-hidden rounded-xl border border-white/5 bg-surface-container p-1 sm:w-auto sm:self-auto">

            @if ($eventoEnVivo && !$eventoEnVivo->isComming())
                <button wire:click="cambiarTab('en_curso')" @class([
                    'min-w-0 flex-1 whitespace-nowrap rounded-lg px-2 py-2 text-[10px] font-black transition-all sm:flex-none sm:px-4 sm:text-xs',
                    'bg-primary text-black shadow-md' => $tabFiltro === 'en_curso',
                    'text-on-surface-variant hover:text-white' => $tabFiltro !== 'en_curso',
                ])>En curso</button>
            @endif

            <button wire:click="cambiarTab('proximos')" @class([
                'min-w-0 flex-1 whitespace-nowrap rounded-lg px-2 py-2 text-[10px] font-black transition-all sm:flex-none sm:px-4 sm:text-xs',
                'bg-primary text-black shadow-md' => $tabFiltro === 'proximos',
                'text-on-surface-variant hover:text-white' => $tabFiltro !== 'proximos',
            ])>Próximos</button>

            @if ($eventoEnVivo && !$eventoEnVivo->isComming())
                <button wire:click="cambiarTab('finalizados')" @class([
                    'min-w-0 flex-1 whitespace-nowrap rounded-lg px-2 py-2 text-[10px] font-black transition-all sm:flex-none sm:px-4 sm:text-xs',
                    'bg-primary text-black shadow-md' => $tabFiltro === 'finalizados',
                    'text-on-surface-variant hover:text-white' => $tabFiltro !== 'finalizados',
                ])>Finalizados</button>
            @endif

            @if ($eventoEnVivo && $eventoEnVivo->isTipoEquipo())

                <button wire:click="cambiarTab('equipos')" @class([
                    'min-w-0 flex-1 whitespace-nowrap rounded-lg px-2 py-2 text-[10px] font-black transition-all sm:flex-none sm:px-4 sm:text-xs',
                    'bg-primary text-black shadow-md' => $tabFiltro === 'equipos',
                    'text-on-surface-variant hover:text-white' => $tabFiltro !== 'equipos',
                ])>Compromisos</button>

            @endif

        </div>
    </div>

    {{-- Contenedor de Listado de Peleas --}}
    <div class="relative w-full">

        <x-site.dashboard.derby-team-cardel
            :peleasCartelera="$peleasCartelera"
            :tabFiltro="$tabFiltro"
            :eventoEnVivo="$eventoEnVivo"
        />

        {{-- Loader nativo del flujo Livewire posicionado de forma absoluta sobre todo el bloque --}}
        <x-layout.loader.fullpage wire:loading.delay.short wire:target="cambiarTab" />

    </div>

</div>
