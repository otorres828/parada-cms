{{--
    PROGRAMACIONES — PASAJEROS Y TARIFAS POR TRAMO
    --------------------------------------------------------------------------
    Muestra la salida programada, su capacidad y la ocupación calculada por origen y destino.
    Presenta las tarifas por origen y destino y los pasajeros de reservas pagadas o pendientes.
    Cada boleto muestra el trayecto de su reserva, asiento, precio final, tasa y abordaje, con
    búsqueda local mediante Alpine.

    Componentes reutilizables utilizados:
    - <x-list.pasaje-qr />: Botón y modal QR para pasajes pagados.
    - <x-list.heading />: Cabecera del módulo con título y acciones.
    - <x-list.status-badge />: Etiqueta visual del estado del registro.
    - <x-layout.loader.fullpage />: Indicador de carga durante las operaciones de Livewire.
    --------------------------------------------------------------------------
--}}

@section('title', 'Programaciones')

<div x-data="passengerProgramacion" class="py-3">

    <x-list.heading>

        <x-slot:title>
            Programaciones @if ($programacion_id)
                <small class="text-body-secondary">#{{ $programacion_id }}</small>
            @endif

        </x-slot:title>

    </x-list.heading>

    <div class="container-fluid px-0 mb-4">

        <div class="row g-3">

            <div class="col-md-6">

                <div class="card h-100">

                    <div class="card-body">

                        <dl class="row mb-0">

                            <dt class="col-sm-4">Empresa</dt>

                            <dd class="col-sm-8">
                                {{ $programacion->viaje?->empresa?->nombre ?? '—' }}
                            </dd>

                            <dt class="col-sm-4">Ruta Principal</dt>

                            <dd class="col-sm-8">
                                @if ($canViajesDetail && $programacion->viaje_id)
                                    <a href="{{ route('admin.viajes.detail', $programacion->viaje_id) }}" wire:navigate
                                        class="fw-bold text-decoration-none">
                                        {{ $programacion->viaje?->origenTerminal?->nombre ?? '—' }} →
                                        {{ $programacion->viaje?->destinoTerminal?->nombre ?? '—' }}
                                        <i class="bi bi-box-arrow-up-right ms-1 text-primary small"></i>
                                    </a>
                                @else
                                    {{ $programacion->viaje?->origenTerminal?->nombre ?? '—' }} →
                                    {{ $programacion->viaje?->destinoTerminal?->nombre ?? '—' }}
                                @endif
                            </dd>

                            <dt class="col-sm-4">Fecha & Hora</dt>

                            <dd class="col-sm-8">
                                {{ $programacion->fecha_salida?->format('d/m/Y') ?? '—' }} a las
                                {{ substr($programacion->hora_salida, 0, 5) }}
                            </dd>

                            <dt class="col-sm-4">Estado</dt>

                            <dd class="col-sm-8">
                                <x-list.status-badge :status="$programacion->estatus" />
                            </dd>

                            <dt class="col-sm-4">Capacidad Bus</dt>

                            <dd class="col-sm-8">{{ $capacidad }} asientos</dd>

                            <dt class="col-sm-4">Pasajes vendidos</dt>

                            <dd class="col-sm-8">{{ $tickets->count() }}</dd>

                            <dt class="col-sm-4">Tasas servicio USD</dt>

                            <dd class="col-sm-8">{{ number_format($tickets->sum('tasa_servicio'), 2) }}</dd>

                        </dl>

                    </div>

                </div>

            </div>

            <div class="col-md-6">

                <div class="card h-100">

                    <div class="card-header fw-semibold">
                        <i class="bi bi-tags me-1" aria-hidden="true"></i> Matriz O&D de Precios Configurados (Salida
                        #{{ $programacion_id }})
                    </div>

                    <div class="card-body">

                        @if ($programacion->tramoPrecios->isNotEmpty())
                            <div class="table-responsive">
                                <table class="table table-sm align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th>Tramo Comercial</th>
                                            <th class="text-end">Precio USD</th>
                                            <th class="text-center">Tope Asientos</th>
                                            <th class="text-center">Ocupados</th>
                                            <th class="text-center">Disponibles</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($programacion->tramoPrecios as $tp)
                                            <tr>
                                                <td>
                                                    <span class="fw-semibold">{{ $tp->origenTerminal?->nombre }}</span>
                                                    <i class="bi bi-arrow-right text-muted mx-1"></i>
                                                    <span
                                                        class="fw-semibold">{{ $tp->destinoTerminal?->nombre }}</span>
                                                </td>
                                                <td class="text-end text-success fw-bold">
                                                    ${{ number_format($tp->precio, 2) }}
                                                </td>
                                                <td class="text-center">
                                                    @if ($tp->asientos_maximos_permitidos !== null)
                                                        <span
                                                            class="badge text-bg-warning">{{ $tp->asientos_maximos_permitidos }}
                                                            asientos</span>
                                                    @else
                                                        <span class="badge text-bg-secondary">Sin tope (Libre)</span>
                                                    @endif
                                                </td>
                                                <td class="text-center">{{ $disponibilidadTramos[$tp->id]['ocupados'] }}</td>
                                                <td class="text-center">{{ $disponibilidadTramos[$tp->id]['disponibles'] }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <p class="small text-body-secondary mt-3 mb-0">
                                Ocupados: asientos de reservas pagadas, pendientes y nuevas sin vencer que coinciden
                                con este tramo. Disponibles = tope de asientos menos ocupados; sin tope se usa la capacidad del autobús.
                            </p>
                        @else
                            <div class="text-body-secondary py-3 text-center">
                                No hay matriz de tarifas O&D configurada para esta salida.
                            </div>
                        @endif

                    </div>

                </div>

            </div>

        </div>

    </div>

    <div class="card">

        <div class="card-header">

            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <span>Pasajeros & Tramos Comercializados</span>

                <div style="width: 320px; max-width: 100%;">

                    <label class="visually-hidden" for="buscar-pasajeros">
                        Buscar pasajero
                    </label>
                    <input id="buscar-pasajeros" type="search" class="form-control" x-model.debounce.200ms="search"
                        placeholder="Buscar pasajero u origen/destino">

                </div>

            </div>

        </div>

        <div class="table-responsive">

            <table class="table align-middle mb-0">

                <thead>

                    <tr>
                        <th>Reserva</th>

                        <th>Pasajero</th>

                        <th>Documento</th>

                        <th>Fecha de nacimiento</th>

                        <th>Asiento</th>

                        <th>Tramo Comprado (Origen ➔ Destino)</th>

                        <th>Subtotal</th>

                        <th>Tasa Servicio</th>

                        <th>Estatus Reserva</th>

                        <th>Abordaje</th>

                        <th class="text-center">QR</th>

                    </tr>
                </thead>

                <tbody>

                    @forelse ($tickets as $ticket)
                        <tr data-search="{{ $ticket->viajero?->nombre }} {{ $ticket->viajero?->apellido }} {{ $ticket->viajero?->documento_identidad }} {{ $ticket->numero_asiento }} {{ $ticket->reserva?->origenTerminal?->nombre }} {{ $ticket->reserva?->destinoTerminal?->nombre }}"
                            x-show="matches($el.dataset.search)">

                            <td>

                                @if ($canReservasDetail)
                                    <a href="{{ route('admin.reservas.detail', $ticket->reserva_id) }}" wire:navigate>
                                        #{{ $ticket->reserva_id }}
                                    </a>
                                @else
                                    #{{ $ticket->reserva_id }}
                                @endif

                            </td>

                            <td>
                                {{ $ticket->viajero?->nombre }} {{ $ticket->viajero?->apellido }}
                            </td>

                            <td>
                                {{ $ticket->viajero?->documento_identidad }}
                            </td>

                            <td>
                                {{ $ticket->viajero?->fecha_nacimiento?->format('d/m/Y') ?? '—' }}
                            </td>

                            <td>
                                <span class="badge text-bg-info">Asiento {{ $ticket->numero_asiento ?? 'S/A' }}</span>
                            </td>

                            <td>
                                <span
                                    class="fw-semibold">{{ $ticket->reserva?->origenTerminal?->nombre ?? 'No registrado' }}</span>
                                <i class="bi bi-arrow-right text-muted mx-1"></i>
                                <span
                                    class="fw-semibold">{{ $ticket->reserva?->destinoTerminal?->nombre ?? 'No registrado' }}</span>
                            </td>

                            <td class="fw-bold text-success">
                                ${{ number_format($ticket->subtotal, 2) }}
                            </td>

                            <td>
                                ${{ number_format($ticket->tasa_servicio, 2) }}
                            </td>

                            <td>
                                {{ $ticket->reserva?->getStatusPago() }}
                            </td>

                            <td>
                                {{ $ticket->abordado ? 'Abordado' : 'Pendiente' }}
                            </td>

                            <td class="text-center">
                                <x-list.pasaje-qr :pasaje="$ticket" />
                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="11" class="text-center py-4">
                                No hay pasajeros registrados en esta salida.
                            </td>

                        </tr>
                    @endforelse

                    @if ($tickets->isNotEmpty())
                        <tr x-cloak x-show="search && !hasMatches()">
                            <td colspan="11" class="text-center py-4">
                                No hay coincidencias.
                            </td>

                        </tr>
                    @endif

                </tbody>
            </table>

        </div>

    </div>

    <x-layout.loader.fullpage wire:loading.delay.short />

</div>

@script
    <script>
        Alpine.data('passengerProgramacion', () => ({
            search: '',
            normalize(value) {
                return String(value ?? '').normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase();
            },
            matches(value) {
                return this.normalize(value).includes(this.normalize(this.search.trim()));
            },
            hasMatches() {
                return [...this.$root.querySelectorAll('[data-search]')].some(row => this.matches(row.dataset
                    .search));
            },
            destroy() {
                this.toastCleanup?.forEach(cleanup => cleanup());
            },
            init() {
                this.toastCleanup = [
                    Livewire.on('successEventList', data => this.$store.toast.success(data.message)),
                    Livewire.on('errorEventList', data => this.$store.toast.info(data.message)),
                ];
                const savedMessage = @js(session()->pull('admin_success'));
                if (savedMessage) this.$nextTick(() => Livewire.dispatch('successEventList', {
                    message: savedMessage
                }));
            },
        }));
    </script>
@endscript
