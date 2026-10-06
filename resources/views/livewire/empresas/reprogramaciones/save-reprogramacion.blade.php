{{--
    REPROGRAMACIÓN — ALTA
    Conserva pasajeros y tramo; permite elegir otra salida y cobrar solo la diferencia.
    Componentes: x-list.heading, x-form.cancel-button, x-layout.error,
    x-empresas.reservas.transporte-taquilla, x-empresas.reservas.pagos-taquilla,
    x-money.dual y x-layout.loader.fullpage.
--}}

@section('title', 'Nueva reprogramación')

<div class="py-3" x-data="saveReprogramacion">

    <x-list.heading>

        <x-slot:title>
            Nueva reprogramación
        </x-slot:title>

        <x-slot:button>

            <x-form.cancel-button :link="route('empresas.reprogramaciones.list')">Volver al listado</x-form.cancel-button>

        </x-slot:button>

    </x-list.heading>

    <x-layout.error />

    <div class="card card-body mb-3">

        <div class="row g-3">

            <div class="col-md-6">

                <label class="form-label" for="reprogramacion-search">Buscar reserva por código, ID o comprador</label>
                <input id="reprogramacion-search" class="form-control" wire:model.live.debounce.500ms="searchReserva"
                    placeholder="Buscar reserva pagada">

            </div>

            <div class="col-md-6">

                <label class="form-label" for="reprogramacion-original">Reserva original</label>
                <select id="reprogramacion-original" class="form-select" wire:model.live="reservaId">
                    <option value="">Seleccionar reserva</option>
                    @foreach ($reservas as $reserva)
                        <option value="{{ $reserva->id }}">{{ $reserva->codigo_referencia }} ·
                            {{ $reserva->nombre_comprador }}</option>
                    @endforeach
                </select>

            </div>

        </div>

    </div>

    @if ($original)
        <div class="row g-4">

            <div class="col-xl-8">

                <div class="card card-body mb-3">

                    <h6>Reserva {{ $original->codigo_referencia }}</h6>
                    <p>{{ $original->nombre_comprador }} · {{ $original->origenTerminal?->nombre }} →
                        {{ $original->destinoTerminal?->nombre }}</p>
                    <p class="small text-muted">Salida original:
                        {{ $original->tramoPrecio?->getSalida()?->format('d/m/Y H:i') }}. Todos los pasajeros y sus
                        descuentos se conservan.</p>

                    <div class="row g-3">

                        <div class="col-md-4">

                            <label class="form-label" for="reprogramacion-fecha">Nueva fecha</label>
                            <x-form.date-input
                                id="reprogramacion-fecha"
                                class="form-control"
                                wire:model.live="fecha"
                                min="{{ today()->toDateString() }}"
                            />

                        </div>

                        <div class="col-md-8">

                            <label class="form-label" for="reprogramacion-salida">Salida disponible para el mismo
                                tramo</label>
                            <select id="reprogramacion-salida" class="form-select" wire:model.live="tarifaId">
                                <option value="">Seleccionar salida</option>
                                @foreach ($opciones as $opcion)
                                    <option value="{{ $opcion->id }}">
                                        {{ $opcion->getSalida()?->format('d/m/Y H:i') }} ·
                                        {{ $opcion->programacion->transporte?->placa }} · USD {{ $opcion->precio }} -
                                        BS
                                        {{ number_format((float) $opcion->precio * (float) $cambio?->valor_usd, 2, ',', '.') }}
                                    </option>
                                @endforeach
                            </select>

                        </div>

                    </div>

                </div>

                <div class="card mb-3">

                    <div class="card-header">Pasajeros de la reserva original</div>

                    <div class="table-responsive">

                        <table class="table mb-0">

                            <thead>

                                <tr>
                                    <th>Pasajero</th>

                                    <th>Asiento</th>

                                    <th>Subtotal original</th>

                                </tr>
                            </thead>

                            <tbody>

                                @foreach ($original->pasajes as $pasaje)

                                    <tr>

                                        <td>
                                            {{ $pasaje->viajero_nombre_completo }}
                                        </td>

                                        <td>
                                            {{ $pasaje->numero_asiento !== null ? 'Se asigna al confirmar' : 'Sin asiento' }}
                                        </td>

                                        <td>

                                            <x-money.dual :usd="$pasaje->subtotal" :bs="$pasaje->calcularMontoBs($pasaje->subtotal)" />

                                        </td>

                                    </tr>

                                @endforeach

                            </tbody>

                        </table>

                    </div>

                </div>

                @if ($tarifa && $diferencia > 0)

                    <x-empresas.reservas.pagos-taquilla
                        :cuentas="$cuentas"
                        :pagos="$pagos"
                        :can-confirm="true"
                        :cambio="$cambio"
                        :habilitado="true"
                    />

                @endif

            </div>

            <div class="col-xl-4">

                <x-empresas.reservas.transporte-taquilla
                    :tarifa="$tarifa"
                    :disponibles="$disponibles"
                    :cantidad="$original->pasajes->whereNotNull('numero_asiento')->count()"
                />

                <div class="card card-body" x-ref="resumenConfirmacion">

                    <h6>Resumen de la reprogramación</h6>
                    <p>{{ $original->codigo_referencia }} · {{ $original->origenTerminal?->nombre }} →
                        {{ $original->destinoTerminal?->nombre }}<br>Nueva salida:
                        {{ $tarifa?->getSalida()?->format('d/m/Y H:i') }} · {{ $original->pasajes->count() }}
                        pasajeros</p>
                    <dl>
                        <dt>Subtotal original pagado</dt>
                        <dd>

                            <x-money.dual
                                :usd="$anterior"
                                :bs="\App\Support\ConversorMoneda::aBolivares($anterior, $cambio)"
                            />

                        </dd>
                        <dt>Subtotal de la nueva reserva</dt>
                        <dd>

                            <x-money.dual
                                :usd="$total"
                                :bs="\App\Support\ConversorMoneda::aBolivares($total, $cambio)"
                            />

                        </dd>
                        <dt>Tasa de servicio</dt>
                        <dd>USD 0.00 / BS 0,00</dd>
                        <dt>Diferencia a pagar</dt>
                        <dd>

                            <x-money.dual
                                :usd="max(0, $diferencia)"
                                :bs="\App\Support\ConversorMoneda::aBolivares(max(0, $diferencia), $cambio)"
                            />

                        </dd>
                        <dt>Pagos añadidos</dt>
                        <dd>

                            <x-money.dual
                                :usd="$abonado"
                                :bs="\App\Support\ConversorMoneda::aBolivares($abonado, $cambio)"
                            />

                        </dd>
                    </dl>
                    @if ($tarifa && $diferencia < 0)
                        <p class="text-danger">No se permite reprogramar a un importe menor.</p>
                    @endif
                    <p class="small text-muted">La reserva original quedará reprogramada. La nueva quedará pagada con
                        sus nuevos QR.</p>
                    <button type="button" class="btn btn-primary" @click="confirmar" wire:loading.attr="disabled"
                        @disabled(!$tarifa || $diferencia < 0 || round($diferencia - $abonado, 2) !== 0.0)>
                        Registrar reprogramación
                    </button>

                </div>

            </div>

        </div>
    @endif

    <x-layout.loader.fullpage wire:loading.delay.short />

</div>

@script
    <script>
        Alpine.data('saveReprogramacion', () => ({
            saving: false,
            resaltados: {
                pagos: false
            },
            init() {
                this.errorCleanup = Livewire.on('empresas_reprogramacion_error', data => this.$store.toast.info(
                    data.message));
            },
            destroy() {
                this.errorCleanup?.();
            },
            async confirmar() {
                if (this.saving) return;
                const resumen = this.$refs.resumenConfirmacion.cloneNode(true);
                resumen.querySelector('button')?.remove();
                resumen.removeAttribute('x-ref');
                const confirmacion = await Swal.fire({
                    html: resumen,
                    title: 'Confirmar reprogramación',
                    text: 'Se conservarán todos los pasajeros y el tramo. La reserva anterior quedará reprogramada y la nueva pagada. Confirma que recibiste la diferencia indicada, si corresponde.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Confirmar y registrar',
                    cancelButtonText: 'Volver a revisar',
                    buttonsStyling: false,
                    customClass: {
                        confirmButton: 'btn btn-primary mx-1',
                        cancelButton: 'btn btn-outline-secondary mx-1',
                    },
                });
                if (!confirmacion.isConfirmed) return;
                this.saving = true;
                try {
                    await this.$wire.save();
                } finally {
                    this.saving = false;
                }
            },
        }));
    </script>
@endscript
