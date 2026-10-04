{{-- Resumen de la cotización mostrado antes de confirmar el cobro. --}}
@props(['tarifa', 'pasajeros', 'precio', 'total', 'abonado', 'cambio'])
<div hidden x-ref="confirmacionReserva">
    @if ($tarifa)
        <div class="text-start" style="font-size: 0.9rem; line-height: 1.5;">
            <p class="text-muted mb-4">Revisa los datos antes de registrar la venta.</p>

            <div class="bg-light border rounded-3 p-3 mb-4">
                <div class="small text-muted mb-1">COMPRADOR</div>
                <div class="fw-semibold text-dark" data-comprador-nombre></div>
                <div class="text-muted" data-comprador-telefono></div>
            </div>

            <div class="mb-4">
                <div class="small text-muted mb-2">DETALLE DEL VIAJE</div>
                <h3 class="h6 text-dark mb-3">{{ $tarifa->origenTerminal->nombre }} → {{ $tarifa->destinoTerminal->nombre }}</h3>
                <div class="row g-3">
                    <div class="col-sm-5">
                        <div class="small text-muted">Salida</div>
                        <div class="fw-semibold">{{ $tarifa->getSalida()?->format('d/m/Y H:i') }}</div>
                    </div>
                    <div class="col-sm-7">
                        <div class="small text-muted">Transporte</div>
                        <div>{{ $tarifa->programacion->transporte->modelo }}</div>
                        <div class="small text-muted">Placa: {{ $tarifa->programacion->transporte->placa }}</div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-2">
                <h3 class="h6 mb-0">Pasajeros</h3>
                <span class="badge bg-light text-dark border">{{ count($pasajeros) }}</span>
            </div>
            <div class="table-responsive border rounded-3 mb-4">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="px-3 py-2">Nombre</th>
                            <th class="px-3 py-2">Tipo de Pasajero</th>
                            <th class="px-3 py-2">Asiento</th>
                            <th class="px-3 py-2 text-end">Precio</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($pasajeros as $persona)
                            @php($ocupa = $persona['tipo_pasajero'] !== 'infante' || ! empty($persona['con_asiento']))
                            <tr>
                                <td class="px-3 py-2">{{ $persona['nombre'] }} {{ $persona['apellido'] }}</td>
                                <td class="px-3 py-2">{{ $persona['tipo_pasajero'] }}</td>
                                <td class="px-3 py-2 text-muted">{{ $ocupa ? 'Asignación automática' : 'Sin asiento' }}</td>
                                <td class="px-3 py-2 text-end">
                                    <span class="text-nowrap">USD {{ number_format($ocupa ? $precio : 0, 2) }}</span> /
                                    <span class="text-nowrap">BS {{ number_format(($ocupa ? $precio : 0) * (float) $cambio?->valor_usd, 2, ',', '.') }}</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="bg-light border rounded-3 p-3 mb-3">
                <div class="d-flex justify-content-between gap-3 mb-2">
                    <span class="text-muted">Subtotal de pasajes</span>
                    <span class="text-end">USD {{ number_format($total, 2) }} / BS {{ number_format($total * (float) $cambio?->valor_usd, 2, ',', '.') }}</span>
                </div>
                <div class="d-flex justify-content-between gap-3 mb-2">
                    <span class="text-muted">Tasa de servicio</span>
                    <span class="text-end">USD 0.00 / BS 0,00</span>
                </div>
                <div class="d-flex justify-content-between align-items-center gap-3 border-top pt-3">
                    <strong class="text-dark">Total de la reserva</strong>
                    <div class="text-end">
                        <div class="fs-5 fw-bold text-dark">USD {{ number_format($total, 2) }}</div>
                        <div class="small text-muted">BS {{ number_format($total * (float) $cambio?->valor_usd, 2, ',', '.') }}</div>
                    </div>
                </div>
            </div>

            <p class="small text-muted mb-0">Al confirmar declaras haber recibido el pago completo. La reserva quedará pagada y sus QR estarán disponibles.</p>
        </div>
    @endif
</div>
