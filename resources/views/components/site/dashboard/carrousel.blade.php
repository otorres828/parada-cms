{{--
|--------------------------------------------------------------------------
| CAROUSEL EVENT SELECTOR COMPONENT
|--------------------------------------------------------------------------
| Renderiza una tira horizontal deslizable y auto-ajustable (Scroll Snap)
| con los eventos destacados en la parte superior del Dashboard. Clasifica
| visualmente los eventos según su estado operativo dinámico (En vivo vs Próximos)
| y gestiona de forma nativa los banners de presentación cargados en la plataforma.
|
| Reusable UI Components:
|   - <x-site.dashboard.event-card /> -> Tarjeta atómica encargada del renderizado individual del evento.
|   - <x-site.geral.title-section />             -> Title para mostrar el titulo de la sección.

|--------------------------------------------------------------------------
--}}
@props(['eventosDestacados'])
@if ($eventosDestacados->count() > 0)

    <section class="mb-8">

        <div class="flex items-center justify-between mb-4">

            <x-site.geral.title-section title="Eventos Destacados" />

        </div>

        <div class="flex gap-4 overflow-x-auto pb-4 custom-scrollbar snap-x">
            @forelse($eventosDestacados as $ev)
                <x-site.dashboard.event-card
                    :status="$ev->estatus === \App\Models\Evento::ESTATUS_EN_CURSO ? 'live' : 'upcoming'"
                    :category="$ev->isTipoEquipo() ? 'Compromiso' : 'Derby'"
                    :title="$ev->nombre"
                    :image="$ev->getBanner()"
                    :eventID="$ev->id"
                    :fecha="$ev->estatus === \App\Models\Evento::ESTATUS_EN_CURSO
                        ? 'En Vivo'
                        : $ev->fecha_formatted" />
            @empty
                <div class="text-on-surface-variant text-sm py-4">No hay eventos activos programados.</div>
            @endforelse
        </div>
    </section>

@endif
