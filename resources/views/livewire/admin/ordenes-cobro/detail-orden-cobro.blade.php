{{--
    ÓRDENES DE COBRO — DETALLE
    --------------------------------------------------------------------------
    Presenta el cálculo congelado, comprobante, comentarios y revisión administrativa.

    Componentes reutilizables utilizados:
    - <x-list.heading />: Cabecera y regreso al listado.
    - <x-form.cancel-button />: Enlace al listado.
    - <x-layout.error />: Errores de validación.
    - <x-layout.loader.fullpage />: Indicador global de carga.
    - Buscador Alpine: Filtra en el navegador las reservas incluidas.
    - Botón Descargar Excel: Exporta las reservas congeladas en la orden.
    --------------------------------------------------------------------------
--}}

@section('title', 'Orden de cobro')

<div x-data="detailOrdenCobro" class="py-3">

    <x-list.heading>

        <x-slot:title>
            Orden de cobro {{ $orden->codigo }}
        </x-slot:title>

        <x-slot:button>
            <x-form.cancel-button :link="route('admin.ordenes-cobro.list')">Volver al listado</x-form.cancel-button>
        </x-slot:button>

    </x-list.heading>

    <x-layout.error />

    <div class="row g-3">

        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-5">Empresa</dt>
                        <dd class="col-sm-7">{{ $orden->empresa->nombre }}</dd>
                        <dt class="col-sm-5">Estado</dt>
                        <dd class="col-sm-7">{{ $orden->getEstatusNombre() }}</dd>
                        <dt class="col-sm-5">Período</dt>
                        <dd class="col-sm-7">{{ $orden->periodo_desde->format('d/m/Y H:i') }} — {{ $orden->periodo_hasta->format('d/m/Y H:i') }}</dd>
                        <dt class="col-sm-5">Emisión</dt>
                        <dd class="col-sm-7">{{ $orden->fecha_emision->format('d/m/Y H:i') }}</dd>
                        <dt class="col-sm-5">Vencimiento</dt>
                        <dd class="col-sm-7">{{ $orden->fecha_vencimiento->format('d/m/Y H:i') }}</dd>
                        <dt class="col-sm-5">Reservas incluidas</dt>
                        <dd class="col-sm-7">{{ $orden->cantidad_reservas }}</dd>
                        <dt class="col-sm-5">Total</dt>
                        <dd class="col-sm-7 fw-bold"><x-money.dual :usd="$orden->total" :bs="$conversionBs['total_bs']" /></dd>
                    </dl>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-5">Referencia</dt>
                        <dd class="col-sm-7">{{ $orden->referencia_pago ?: '—' }}</dd>
                        <dt class="col-sm-5">Pago reportado</dt>
                        <dd class="col-sm-7">{{ $orden->fecha_pago_reportado?->format('d/m/Y H:i') ?: '—' }}</dd>
                        <dt class="col-sm-5">Aprobación</dt>
                        <dd class="col-sm-7">{{ $orden->fecha_aprobacion?->format('d/m/Y H:i') ?: '—' }}</dd>
                        <dt class="col-sm-5">Comprobante</dt>
                        <dd class="col-sm-7">
                            @if ($orden->comprobante)
                                <a href="{{ Storage::url($orden->comprobante) }}" target="_blank">Ver comprobante</a>
                            @else
                                —
                            @endif
                        </dd>
                    </dl>

                    @if ($orden->comentarios)
                        <hr>
                        <div class="small" style="white-space: pre-line;">{{ $orden->comentarios }}</div>
                    @endif
                </div>
            </div>
        </div>

    </div>

    <div class="card mt-3">

        <div class="card-header">

            <div class="d-flex flex-column flex-lg-row align-items-lg-center w-100 gap-3">

                <span class="fw-semibold">Reservas incluidas</span>

                <div class="d-flex flex-column flex-sm-row align-items-stretch gap-2 ms-lg-auto">

                    <div class="input-group" style="width: 280px; max-width: 100%;">
                        <span class="input-group-text">
                            <i class="bi bi-search" aria-hidden="true"></i>
                        </span>
                        <input type="search" class="form-control" x-model.debounce.300ms="search"
                            placeholder="Buscar reserva" aria-label="Buscar reserva">
                    </div>

                    @if ($canDownload)
                        <button type="button" class="btn btn-success text-nowrap" wire:click="exportExcel"
                            wire:loading.attr="disabled" wire:target="exportExcel">
                            <i class="bi bi-file-earmark-excel" aria-hidden="true"></i> Descargar Excel
                        </button>
                    @endif

                </div>

            </div>

        </div>

        <div class="table-responsive">
            <table class="table table-striped align-middle mb-0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Código</th>
                        <th>Fecha de pago</th>
                        <th class="text-end">Tasa de servicio</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($orden->reservas_incluidas ?? [] as $reserva)
                        <tr x-show="matches(@js([
                            $reserva['reserva_id'],
                            $reserva['codigo_referencia'],
                            $reserva['fecha_pago'],
                            $reserva['tasa_servicio'],
                        ]))">
                            <td>{{ $reserva['reserva_id'] }}</td>
                            <td>{{ $reserva['codigo_referencia'] }}</td>
                            <td>{{ $reserva['fecha_pago'] ? \Carbon\Carbon::parse($reserva['fecha_pago'])->format('d/m/Y H:i') : '—' }}</td>
                            <td class="text-end">
                                <x-money.dual :usd="$reserva['tasa_servicio']"
                                    :bs="$conversionBs['reservas_bs'][$reserva['reserva_id']] ?? null" />
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    @if ($canReview && $orden->estatus === \App\Models\OrdenCobro::ESTATUS_PENDIENTE)
        <div class="card mt-3">
            <div class="card-body">
                <label class="form-label" for="motivo">Motivo del rechazo</label>
                <textarea id="motivo" class="form-control" rows="3" wire:model="motivo"></textarea>
                <div class="d-flex justify-content-end gap-2 mt-3">
                    <button class="btn btn-outline-danger" type="button" wire:click="rechazar">Rechazar</button>
                    <button class="btn btn-success" type="button" wire:click="aprobar">Aprobar</button>
                </div>
            </div>
        </div>
    @endif

    <x-layout.loader.fullpage wire:loading.delay.short />

</div>

@script
    <script>
        Alpine.data('detailOrdenCobro', () => ({
            search: '',

            init() {
                this.toastCleanup = [
                    Livewire.on('successEventList', data => this.$store.toast.success(data.message)),
                    Livewire.on('errorEventList', data => this.$store.toast.info(data.message)),
                ];
            },
            destroy() {
                this.toastCleanup?.forEach(cleanup => cleanup());
            },

            matches(values) {
                const term = this.search.toString().trim().toLocaleLowerCase('es');

                if (term === '') {
                    return true;
                }

                return values.some(value => value?.toString().toLocaleLowerCase('es').includes(term));
            },
        }));
    </script>
@endscript
