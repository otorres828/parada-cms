@section('title', 'Reservas y ventas')

<div x-data="detailReserva" class="py-3">

    <x-list.heading>

        <x-slot:title>
            Reservas y ventas @if ($reserva_id)
                <small class="text-body-secondary">#{{ $reserva_id }}</small>
            @endif

        </x-slot:title>

        <x-slot:button>

            @if (\App\Services\Admin\Access::allows('reservas', 'list'))
                <x-form.cancel-button :link="route('admin.reservas.list')">
                    Volver al listado
                </x-form.cancel-button>
            @endif

        </x-slot:button>

    </x-list.heading>



    <div class="container-fluid px-0 mb-4">

        <div class="row">

            <div class="col-md-6">

                <div class="card">

                    <div class="card-body">

                        <dl class="row mb-0">
                            <dt class="col-sm-4">Referencia</dt>
                            <dd class="col-sm-8">
                                {{ $reserva->codigo_referencia ?? '—' }}
                            </dd>
                            <dt class="col-sm-4">Cliente</dt>
                            <dd class="col-sm-8">
                                {{ $reserva->usuario?->name ?? '—' }}
                            </dd>
                            <dt class="col-sm-4">Empresa</dt>
                            <dd class="col-sm-8">
                                {{ $reserva->programacion?->viaje?->empresa?->nombre ?? '—' }}
                            </dd>
                            <dt class="col-sm-4">Fecha</dt>
                            <dd class="col-sm-8">
                                {{ $reserva->fecha_compra?->format('d/m/Y H:i') ?? '—' }}
                            </dd>
                            <dt class="col-sm-4">Total USD</dt>
                            <dd class="col-sm-8">
                                {{ number_format($reserva->monto_total ?? 0, 2) }}
                            </dd>
                            <dt class="col-sm-4">Estado</dt>
                            <dd class="col-sm-8">
                                <x-list.status-badge :status="$reserva->estado_pago" />
                            </dd>
                            <dt class="col-sm-4">Tasa de servicio USD</dt>
                            <dd class="col-sm-8">{{ number_format($reserva->tasa_servicio, 2) }}</dd>
                            <dt class="col-sm-4">Programación</dt>
                            <dd class="col-sm-8">
                                @if (\App\Services\Admin\Access::allows('programaciones', 'passengers'))
                                    <a href="{{ route('admin.programaciones.passengers', $reserva->programacion_id) }}" wire:navigate>
                                        Ver programación #{{ $reserva->programacion_id }}
                                    </a>
                                @else
                                    #{{ $reserva->programacion_id }}
                                @endif
                            </dd>
                        </dl>

                    </div>

                </div>

            </div>

        </div>

    </div>

    @php($tickets = $reserva->pasajes)

    <div class="card">

        <div class="card-header">
            Pasajeros
        </div>

        <div class="table-responsive">

            <table class="table align-middle">

                <thead>

                    <tr>
                        <th>Nombre</th>
                        <th>Documento</th>
                        <th class="text-end">Precio base USD</th>
                        <th class="text-end">Descuento USD</th>
                        <th class="text-end">Precio final USD</th>
                        <th class="text-end">Tasa de servicio USD</th>
                        <th class="text-end">Precio + tasa USD</th>
                        <th>Abordaje</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($tickets as $ticket)

                        <tr>
                            <td>{{ $ticket->viajero->nombre }} {{ $ticket->viajero->apellido }}</td>
                            <td>{{ $ticket->viajero->documento_identidad }}</td>
                            <td class="text-end">{{ number_format($ticket->precio_base, 2) }}</td>
                            <td class="text-end">{{ number_format($ticket->descuento, 2) }}</td>
                            <td class="text-end">{{ number_format($ticket->precio_final, 2) }}</td>
                            <td class="text-end">{{ number_format($ticket->tasa_servicio, 2) }}</td>
                            <td class="text-end">
                                {{ number_format(bcadd($ticket->precio_final, $ticket->tasa_servicio, 2), 2) }}</td>
                            <td>{{ $ticket->abordado ? 'Abordado' : 'Pendiente' }}</td>
                        </tr>
                    @empty

                        <tr>
                            <td colspan="9">No hay pasajeros.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

        </div>

    </div>

    <x-layout.loader.fullpage wire:loading.delay.short />

</div>

@script
    <script>
        Alpine.data('detailReserva', () => ({
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
