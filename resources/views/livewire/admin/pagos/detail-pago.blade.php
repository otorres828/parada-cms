@section('title', 'Pagos recibidos')

<div x-data="detailPago" class="py-3">

    <x-list.heading>

        <x-slot:title>
            Pagos recibidos @if ($pago_id)
                <small class="text-body-secondary">#{{ $pago_id }}</small>

            @endif

        </x-slot:title>

        <x-slot:button>

            @if (\App\Services\Admin\Access::allows('pagos', 'list'))

                <x-form.cancel-button :link="route('admin.pagos.list')">
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
                            <dd class="col-sm-8">{{ $pago->referencia ?? '—' }}</dd>
                            <dt class="col-sm-4">Reserva</dt>
                            <dd class="col-sm-8">{{ $pago->reserva?->codigo_referencia ?? '—' }}
                            </dd>
                            <dt class="col-sm-4">Empresa</dt>
                            <dd class="col-sm-8">{{ $pago->empresa?->nombre ?? '—' }}</dd>
                            <dt class="col-sm-4">Recibido USD</dt>
                            <dd class="col-sm-8">{{ number_format($pago->monto ?? 0, 2) }}</dd>
                            <dt class="col-sm-4">Neto empresa USD</dt>
                            <dd class="col-sm-8">{{ number_format($pago->neto_empresa ?? 0, 2) }}</dd>
                            <dt class="col-sm-4">Fecha</dt>
                            <dd class="col-sm-8">{{ $pago->fecha_pago?->format('d/m/Y H:i') ?? '—' }}</dd>
                            <dt class="col-sm-4">Tasa de servicio USD</dt>
                            <dd class="col-sm-8">{{ number_format($pago->reserva->tasa_servicio, 2) }}</dd>

                            @if ($pago->comision > 0)

                                <dt class="col-sm-4">Comisión histórica USD</dt>
                                <dd class="col-sm-8">{{ number_format($pago->comision, 2) }}</dd>

                            @endif

                            <dt class="col-sm-4">Observaciones</dt>
                            <dd class="col-sm-8">{{ $pago->comentario ?? '—' }}</dd>
                        </dl>

                    </div>

                </div>

            </div>

        </div>

    </div>

    @if ($pago->comprobante)

        <button class="btn btn-outline-primary mb-3" type="button" wire:click="downloadProof">Descargar comprobante</button>

    @endif

    <div class="card mb-4">

        <div class="card-header">
            Viajeros de la reserva · {{ $pago->reserva->codigo_referencia }}
        </div>

        <div class="table-responsive">

            <table class="table align-middle mb-0">

                <thead>

                    <tr>
                        <th>Viajero</th>

                        <th>Documento</th>

                        <th>Asiento</th>

                        <th>Precio base USD</th>

                        <th>Descuento USD</th>

                        <th>Precio final USD</th>

                        <th>Tipo de tasa</th>

                        <th>Valor aplicado</th>

                        <th>Tasa cobrada USD</th>

                        <th>Total USD</th>

                    </tr>
                </thead>

                <tbody>

                    @forelse($pago->reserva->pasajes as $pasaje)

                        <tr>
                            <td>
                                {{ $pasaje->viajero?->nombre }} {{ $pasaje->viajero?->apellido }}
                            </td>

                            <td>
                                {{ $pasaje->viajero?->documento_identidad }}
                            </td>

                            <td>
                                {{ $pasaje->numero_asiento ?? 'Sin asignar' }}
                            </td>

                            <td>
                                {{ number_format($pasaje->precio_base, 2) }}
                            </td>

                            <td>
                                {{ number_format($pasaje->descuento, 2) }}
                            </td>

                            <td>
                                {{ number_format($pasaje->precio_final, 2) }}
                            </td>

                            <td>
                                {{ $pasaje->tipo_servicio === 2 ? 'Porcentaje' : ($pasaje->tipo_servicio === 1 ? 'Monto fijo' : 'Histórica') }}
                            </td>

                            <td>
                                {{ $pasaje->valor_servicio !== null ? number_format($pasaje->valor_servicio, 2) . ($pasaje->tipo_servicio === 2 ? ' %' : ' USD') : 'No registrado' }}
                            </td>

                            <td>
                                {{ number_format($pasaje->tasa_servicio, 2) }}
                            </td>

                            <td>
                                {{ number_format(bcadd($pasaje->precio_final, $pasaje->tasa_servicio, 2), 2) }}
                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="10" class="text-center py-4">
                                No hay viajeros registrados en esta reserva.
                            </td>

                        </tr>

                    @endforelse

                </tbody>
                <tfoot>

                    <tr>
                        <th colspan="8" class="text-end">Total de la reserva</th>

                        <th>{{ number_format($pago->reserva->tasa_servicio, 2) }}</th>

                        <th>{{ number_format($pago->monto, 2) }}</th>

                    </tr>
                </tfoot>
            </table>

        </div>

    </div>

    <x-layout.loader.fullpage wire:loading.delay.short />

</div>

@script
    <script>
        Alpine.data('detailPago', () => ({
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
