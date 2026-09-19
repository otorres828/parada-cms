@section('title', 'Programaciones')

<div x-data="passengerProgramacion" class="py-3">

    <x-list.heading>

        <x-slot:title>
            Programaciones @if ($programacion_id)
                <small class="text-body-secondary">#{{ $programacion_id }}</small>
            @endif

        </x-slot:title>

    </x-list.heading>

    @if ($errors->any())

        <div class="alert alert-danger" role="alert">

            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>

        </div>

    @endif

    <div class="container-fluid px-0 mb-4">

        <div class="row">

            <div class="col-md-6">

                <div class="card">

                    <div class="card-body">

                        <dl class="row mb-0">

                            <dt class="col-sm-4">Empresa</dt>

                            <dd class="col-sm-8">
                                {{ $programacion->viaje?->empresa?->nombre ?? '—' }}
                            </dd>
                            
                            <dt class="col-sm-4">Origen</dt>

                            <dd class="col-sm-8">
                                {{ $programacion->viaje?->origenTerminal?->nombre ?? '—' }}
                            </dd>

                            <dt class="col-sm-4">Destino</dt>

                            <dd class="col-sm-8">
                                {{ $programacion->viaje?->destinoTerminal?->nombre ?? '—' }}
                            </dd>
                            <dt class="col-sm-4">Fecha</dt>
                            
                            <dd class="col-sm-8">
                                {{ $programacion->fecha_salida?->format('d/m/Y') ?? '—' }}
                            </dd>

                            <dt class="col-sm-4">Hora</dt>

                            <dd class="col-sm-8">
                                {{ $programacion->hora_salida ?? '—' }}
                            </dd>
                           
                            <dt class="col-sm-4">Precio USD</dt>

                            <dd class="col-sm-8">
                                {{ number_format($programacion->precio_pasaje ?? 0, 2) }}
                            </dd>

                            <dt class="col-sm-4">Estado</dt>

                            <dd class="col-sm-8">
                                <x-list.status-badge :status="$programacion->estatus" />
                            </dd>
                            
                            <dt class="col-sm-4">Capacidad</dt>

                            <dd class="col-sm-8">{{ $capacidad }} asientos</dd>

                            <dt class="col-sm-4">Disponibles</dt>

                            <dd class="col-sm-8">{{ $programacion->asientos_disponibles ?? '—' }}</dd>

                            <dt class="col-sm-4">Asientos ocupados</dt>

                            <dd class="col-sm-8">{{ $ocupados }}</dd>

                            <dt class="col-sm-4">Pasajes vendidos</dt>

                            <dd class="col-sm-8">{{ $tickets->count() }}</dd>

                            <dt class="col-sm-4">Ocupación</dt>

                            <dd class="col-sm-8">
                                {{ number_format($ocupacion, 2) }} % 
                                <small class="text-body-secondary">según disponibilidad registrada</small>
                            </dd>

                            <dt class="col-sm-4">Tasas de servicio USD</dt>

                            <dd class="col-sm-8">{{ number_format($tickets->sum('tasa_servicio'), 2) }}</dd>

                        </dl>

                    </div>

                </div>

            </div>

        </div>

    </div>

    <div class="card">

        <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-3">
            <span>Pasajeros</span>

            <div style="width: 320px; max-width: 100%;">

                <label class="visually-hidden" for="buscar-pasajeros">
                    Buscar pasajero
                </label>
                <input id="buscar-pasajeros" type="search" class="form-control" x-model.debounce.200ms="search"
                    placeholder="Buscar pasajero">

            </div>

        </div>

        <div class="table-responsive">

            <table class="table">

                <thead>

                    <tr>
                        <th>Reserva</th>

                        <th>Nombre</th>

                        <th>Documento</th>

                        <th>Fecha de nacimiento</th>

                        <th>Precio base USD</th>

                        <th>Descuento USD</th>

                        <th>Precio final USD</th>

                        <th>Tasa de servicio USD</th>

                        <th>Estatus de reserva</th>

                        <th>Abordaje</th>

                    </tr>
                </thead>

                <tbody>
                    @forelse ($tickets as $ticket)

                        <tr data-search="{{ $ticket->viajero?->nombre }} {{ $ticket->viajero?->apellido }} {{ $ticket->viajero?->documento_identidad }} {{ $ticket->numero_asiento }}"
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

                            <td>{{ $ticket->viajero->nombre }} {{ $ticket->viajero->apellido }}</td>

                            <td>{{ $ticket->viajero->documento_identidad }}</td>

                            <td>{{ $ticket->viajero?->fecha_nacimiento?->format('d/m/Y') ?? 'Sin registrar' }}</td>

                            <td>{{ number_format($ticket->precio_base, 2) }}</td>

                            <td>{{ number_format($ticket->descuento, 2) }}</td>

                            <td>{{ number_format($ticket->precio_final, 2) }}</td>

                            <td>{{ number_format($ticket->tasa_servicio, 2) }}</td>

                            <td>{{ $ticket->reserva->getStatusPago() }}</td>

                            <td>{{ $ticket->abordado ? 'Abordado' : 'Pendiente' }}</td>

                        </tr>
                    @empty

                        <tr>
                            <td colspan="10">No hay pasajeros.</td>
                        </tr>
                        @endforelse @if ($tickets->isNotEmpty())

                            <tr x-cloak x-show="search && !hasMatches()">
                                <td colspan="10" class="text-center py-4">No hay coincidencias.</td>
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
                return [...this.$root.querySelectorAll('[data-search]')].some(row => this.matches(row.dataset.search));
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
