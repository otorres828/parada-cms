{{--
    PASAJES — DETALLE
    --------------------------------------------------------------------------
    Presenta la fecha de reserva, la fecha y hora de salida de la programación, el viajero, su documento, asiento y estado de abordaje. Consulta el
    origen y destino comprados a través de la reserva y muestra el precio del boleto, el estado de
    pago y la tasa de servicio aplicada con su modalidad, valor y rango histórico.
    Muestra el QR generado localmente con el localizador únicamente si la reserva está pagada.

    Componentes reutilizables utilizados:
    - <x-list.heading />: Cabecera del módulo con título y acciones.
    - <x-form.cancel-button />: Enlace para regresar o cancelar la edición.
    - <x-list.status-reserva />: Etiqueta del estado de pago según las constantes de Reserva.
    - <x-layout.loader.fullpage />: Indicador de carga durante las operaciones de Livewire.
    --------------------------------------------------------------------------
--}}

@section('title', 'Pasajes')

<div x-data="detailPasaje" class="py-3">

    <x-list.heading>

        <x-slot:title>
            Pasajes @if ($pasaje_id)
                <small class="text-body-secondary">#{{ $pasaje_id }}</small>
            @endif

        </x-slot:title>

        <x-slot:button>

            <x-form.cancel-button :link="route('admin.pasajes.list')">
                Volver al listado
            </x-form.cancel-button>

        </x-slot:button>

    </x-list.heading>

    <div class="container-fluid px-0 mb-4">

        <div class="row g-3">

            <div class="col-md-6">

                <div class="card">

                    <div class="card-body">

                        <dl class="row mb-0">

                            <dt class="col-sm-4">Ruta</dt>

                            <dd class="col-sm-8">
                                {{ $pasaje->reserva?->origenTerminal?->nombre ?? 'No registrado' }} -                                 {{ $pasaje->reserva?->destinoTerminal?->nombre ?? 'No registrado' }}
                            </dd>

                            <dt class="col-sm-4">Reserva</dt>

                            <dd class="col-sm-8">
                                {{ $pasaje->reserva?->codigo_referencia ?? '—' }}
                            </dd>

                            <dt class="col-sm-4">Cupón aplicado</dt>

                            <dd class="col-sm-8">
                                {{ $pasaje->reserva?->cupon?->codigo ?? 'Sin cupón' }}
                            </dd>

                            <dt class="col-sm-4">Fecha de reserva</dt>

                            <dd class="col-sm-8">
                                {{ $pasaje->reserva?->fecha_compra?->format('d/m/Y H:i') ?? '—' }}
                            </dd>

                            <dt class="col-sm-4">Horario de salida</dt>

                            <dd class="col-sm-8">
                                {{ $pasaje->reserva?->programacion?->fecha_salida?->format('d/m/Y') ?? '—' }}                                 {{ $pasaje->reserva?->programacion?->hora_salida ? substr($pasaje->reserva->programacion->hora_salida, 0, 5) : '—' }}

                            </dd>

                            <dt class="col-sm-4">Viajero</dt>

                            <dd class="col-sm-8">{{ $pasaje->viajero?->nombre ?? '—' }}</dd>

                            <dt class="col-sm-4">Documento</dt>

                            <dd class="col-sm-8">
                                {{ $pasaje->viajero?->documento_identidad ?? '—' }}
                            </dd>

                            <dt class="col-sm-4">Fecha de nacimiento</dt>

                            <dd class="col-sm-8">
                                {{ $pasaje->viajero?->fecha_nacimiento?->format('d/m/Y') ?? '—' }}
                            </dd>

                            <dt class="col-sm-4">Asiento</dt>

                            <dd class="col-sm-8">{{ $pasaje->numero_asiento ?? '—' }}</dd>

                            <dt class="col-sm-4">Abordado</dt>

                            <dd class="col-sm-8">
                                {{ $pasaje->abordado ? 'Sí' : 'No' }}
                                @if ($pasaje->abordado && $pasaje->hora_abordaje)
                                    a las {{ substr($pasaje->hora_abordaje, 0, 5) }}
                                @endif
                            </dd>

                            <dt class="col-sm-4">Precio USD</dt>

                            <dd class="col-sm-8">{{ number_format($pasaje->precio_base, 2) }}</dd>

                            <dt class="col-sm-4">Descuento USD</dt>

                            <dd class="col-sm-8">{{ number_format($pasaje->descuento, 2) }}</dd>

                            <dt class="col-sm-4">Subtotal USD</dt>

                            <dd class="col-sm-8">{{ number_format($pasaje->subtotal, 2) }}</dd>

                            <dt class="col-sm-4">Tasa de servicio USD</dt>

                            <dd class="col-sm-8">{{ number_format($pasaje->tasa_servicio, 2) }}</dd>

                            <dt class="col-sm-4">Total USD</dt>

                            <dd class="col-sm-8">{{ number_format($pasaje->total, 2) }}</dd>

                            <dt class="col-sm-4">Pago</dt>

                            <dd class="col-sm-8">
                                <x-list.status-reserva :status="$pasaje->reserva->estado_pago" />
                            </dd>

                            <dt class="col-sm-4">Tipo de tasa aplicada</dt>

                            <dd class="col-sm-8">
                                {{ $pasaje->tipo_servicio === 2 ? 'Porcentaje' : ($pasaje->tipo_servicio === 1 ? 'Monto fijo' : 'Registro histórico') }}
                            </dd>

                            <dt class="col-sm-4">Valor aplicado</dt>

                            <dd class="col-sm-8">
                                {{ $pasaje->valor !== null ? number_format($pasaje->valor, 2) . ($pasaje->tipo_servicio === 2 ? ' %' : ' USD') : 'No registrado' }}
                            </dd>

                            <dt class="col-sm-4">Rango aplicado USD</dt>

                            <dd class="col-sm-8">
                                @if ($pasaje->monto_minimo !== null)
                                    {{ number_format($pasaje->monto_minimo, 2) }} —
                                    {{ $pasaje->monto_maximo !== null ? number_format($pasaje->monto_maximo, 2) : 'Sin límite' }}
                                @else
                                    No registrado
                                @endif
                            </dd>

                        </dl>

                    </div>

                </div>

            </div>

            @if ($qr)
            <div class="col-md-6">

                <div class="card">

                    <div class="card-header">Código QR del pasaje</div>

                    <div class="card-body text-center">

                            <img src="{{ $qr }}" alt="Código QR del pasaje #{{ $pasaje->id }}"
                                width="256" height="256" class="img-fluid bg-white">

                            <p class="small text-body-secondary text-break mt-3 mb-0">
                                {{ $pasaje->localizador }}
                            </p>

                    </div>

                </div>

            </div>

            @endif

        </div>

    </div>

    <x-layout.loader.fullpage wire:loading.delay.short />

</div>

@script
    <script>
        Alpine.data('detailPasaje', () => ({
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
