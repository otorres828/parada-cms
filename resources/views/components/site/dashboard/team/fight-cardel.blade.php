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
    {{ $attributes->merge(['class' => 'glass-panel rounded-3xl p-6 border border-white/5 bg-surface-container-low/30']) }}>

    {{-- Encabezado y Filtros Reactivos --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">

        <x-site.geral.title-section title="Cartelera de Peleas" />

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
