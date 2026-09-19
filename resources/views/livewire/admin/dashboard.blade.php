{{--
    RESUMEN DE LA PLATAFORMA
    --------------------------------------------------------------------------
    Presenta el resumen administrativo de ventas, tasas de servicio, reservas y empresas. Incluye
    estados de las compras, últimas reservas y próximas salidas para el período seleccionado.

    Componentes reutilizables utilizados:

    --------------------------------------------------------------------------
--}}

@section('title', 'Resumen de la plataforma')

<div class="container-fluid py-4">

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">

        <div>

            <div class="text-primary small fw-semibold text-uppercase mb-1">
                Parada · Administración
            </div>

            <h1 class="h3 fw-bold mb-1">Resumen de la plataforma</h1>
            <p class="text-body-secondary mb-0">Supervisa las empresas, la operación y la venta de pasajes.</p>

        </div>

        <a href="{{ route('admin.empresas.add') }}" class="btn btn-primary" wire:navigate>
            <i class="bi bi-plus-lg me-2" aria-hidden="true"></i>Registrar empresa
        </a>

    </div>

    <div class="card border-0 shadow-sm mb-4">

        <div class="card-body d-flex flex-wrap align-items-center justify-content-between gap-3">

            <div>

                <h2 class="h6 fw-bold mb-1">Ventas del período</h2>

                <div class="text-body-secondary small">

                    {{ $desde->format('d/m/Y') }} — {{ $hasta->format('d/m/Y H:i') }}
                    <span wire:loading class="ms-2 text-primary" role="status">Actualizando…</span>

                </div>

            </div>

            <div class="d-flex align-items-center gap-2">

                <label for="dashboard-periodo" class="small text-body-secondary">
                    Período
                </label>

                <select id="dashboard-periodo" class="form-select form-select-sm w-auto" wire:model.live="periodo">

                    <option value="hoy">Hoy</option>
                    <option value="7">Últimos 7 días</option>
                    <option value="30">Últimos 30 días</option>
                    <option value="mes">Este mes</option>

                </select>

                <button type="button" class="btn btn-outline-secondary btn-sm" wire:click="$refresh" wire:loading.attr="disabled"
                    aria-label="Actualizar indicadores">
                    <i class="bi bi-arrow-clockwise" aria-hidden="true"></i>
                </button>

            </div>

        </div>

    </div>

    <div class="row g-3 mb-2" wire:loading.class="opacity-50">

        @foreach ([['label' => 'Ventas pagadas', 'value' => number_format($metrics['ventas'], 2, ',', '.'), 'note' => $metrics['reservas_pagadas'] . ' reservas pagadas', 'icon' => 'cash-stack', 'color' => 'primary'], ['label' => 'Pasajes vendidos', 'value' => number_format($metrics['pasajes'], 0, ',', '.'), 'note' => 'Pasajes de reservas pagadas', 'icon' => 'ticket-perforated', 'color' => 'success'], ['label' => 'Tasas de servicio', 'value' => number_format($metrics['tasas'], 2, ',', '.'), 'note' => 'Incluidas en las ventas pagadas', 'icon' => 'receipt', 'color' => 'info'], ['label' => 'Reservas pendientes', 'value' => number_format($metrics['pendientes'], 0, ',', '.'), 'note' => 'Con estado de pago pendiente', 'icon' => 'hourglass-split', 'color' => 'warning']] as $card)

            <div class="col-sm-6 col-xl-3">

                <div class="card h-100 border-0 shadow-sm">

                    <div class="card-body p-4">

                        <div class="d-flex justify-content-between align-items-center mb-3">

                            <h2 class="h6 text-body-secondary mb-0">{{ $card['label'] }}</h2>
                            <i class="bi bi-{{ $card['icon'] }} fs-4 text-{{ $card['color'] }}" aria-hidden="true"></i>

                        </div>

                        <div class="h2 fw-bold mb-2">
                            {{ $card['value'] }}
                        </div>

                        <div class="small text-body-secondary">
                            {{ $card['note'] }}
                        </div>

                    </div>

                </div>

            </div>

        @endforeach

    </div>

    <p class="small text-body-secondary mb-4">Importes en la moneda de operación. Calculados por fecha de compra y estado actual de la
        reserva; excluyen cancelaciones y reembolsos. Las tasas no representan utilidad neta.</p>

    <div class="row g-3 mb-4">

        <div class="col-lg-8">

            <div class="card h-100 border-0 shadow-sm">

                <div class="card-header bg-transparent border-0 pt-4 px-4">

                    <h2 class="h5 fw-bold mb-1">Estado de las reservas</h2>
                    <p class="small text-body-secondary mb-0">{{ $totalReservas }} reservas en el período seleccionado</p>

                </div>

                <div class="card-body px-4">

                    @if ($totalReservas === 0)

                        <div class="text-center text-body-secondary py-4">

                            <i class="bi bi-ticket-perforated fs-1 d-block mb-2" aria-hidden="true"></i>
                            Aún no hay reservas en este período.

                        </div>

                    @else

                        <div class="row g-3">

                            @foreach ($estados as $estado => $datos)

                                <div class="col-sm-6" wire:key="estado-{{ $estado }}">

                                    <div class="d-flex justify-content-between small mb-2">

                                        <span>{{ $datos['label'] }}</span>
                                        <span class="fw-semibold">{{ $datos['cantidad'] }} <span class="text-body-secondary fw-normal">·
                                                {{ $datos['porcentaje'] }}%</span></span>

                                    </div>

                                    <div class="progress" style="height: 6px" role="progressbar" aria-label="{{ $datos['label'] }}"
                                        aria-valuenow="{{ $datos['porcentaje'] }}" aria-valuemin="0" aria-valuemax="100">

                                        <div class="progress-bar bg-{{ $datos['color'] }}" style="width: {{ $datos['porcentaje'] }}%">

                                        </div>

                                    </div>

                                </div>

                            @endforeach

                        </div>

                    @endif

                </div>

            </div>

        </div>

        <div class="col-lg-4">

            <div class="card h-100 border-0 shadow-sm">

                <div class="card-body p-4">

                    <h2 class="h5 fw-bold mb-1">Operación actual</h2>
                    <p class="small text-body-secondary mb-4">Indicadores independientes del período de ventas.</p>

                    <div class="d-flex justify-content-between align-items-center mb-3">

                        <span>Empresas activas</span>
                        <span class="fw-bold fs-5">{{ $metrics['empresas_activas'] }} <small class="text-body-secondary fw-normal">/
                                {{ $metrics['empresas'] }}</small></span>

                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-4">

                        <span>Salidas en los próximos 7 días</span>
                        <span class="fw-bold fs-5">{{ $metrics['salidas'] }}</span>

                    </div>

                    <a href="{{ route('admin.empresas.list') }}" class="btn btn-outline-primary btn-sm" wire:navigate>Gestionar empresas <i
                            class="bi bi-arrow-right ms-1" aria-hidden="true"></i></a>

                </div>

            </div>

        </div>

    </div>

    <div class="card border-0 shadow-sm mb-4">

        <div class="card-header bg-transparent border-0 px-4 pt-4 d-flex flex-wrap align-items-center justify-content-between gap-2">

            <h2 class="h5 fw-bold mb-0">Reservas recientes del período</h2>
            <a href="{{ route('admin.reservas.list') }}" class="small" wire:navigate>Ver todas las reservas</a>

        </div>

        <div class="card-body px-0">

            <div class="table-responsive">

                <table class="table table-hover align-middle mb-0">

                    <thead class="table-light">

                        <tr>
                            <th scope="col" class="ps-4">Referencia / fecha</th>

                            <th scope="col">Cliente</th>

                            <th scope="col">Empresa</th>

                            <th scope="col" class="text-center">Pasajes</th>

                            <th scope="col" class="text-end">Importe</th>

                            <th scope="col" class="pe-4">Estado de pago</th>

                        </tr>
                    </thead>

                    <tbody>

                        @forelse ($ultimasReservas as $reserva)

                            <tr wire:key="reserva-{{ $reserva->id }}">
                                <td class="ps-4">
                                    <a href="{{ route('admin.reservas.detail', ['reserva_id' => $reserva->id]) }}"
                                        class="fw-semibold text-decoration-none" wire:navigate>{{ $reserva->codigo_referencia }}</a>

                                    <div class="small text-body-secondary">
                                        {{ $reserva->fecha_compra->format('d/m/Y H:i') }}
                                    </div>
                                </td>

                                <td>
                                    {{ $reserva->usuario?->name ?? 'Sin cliente' }}
                                </td>

                                <td>
                                    {{ $reserva->programacion?->viaje?->empresa?->nombre ?? 'Sin empresa' }}
                                </td>

                                <td class="text-center">
                                    {{ $reserva->pasajes_count }}
                                </td>

                                <td class="text-end text-nowrap">
                                    {{ number_format($reserva->monto_total, 2, ',', '.') }}
                                </td>

                                <td class="pe-4">
                                    <span
                                        class="badge text-bg-{{ $estados[$reserva->estado_pago]['color'] ?? 'secondary' }}">{{ ucfirst($reserva->getStatusPago()) }}</span>
                                </td>

                            </tr>

                        @empty

                            <tr>
                                <td colspan="6" class="text-center text-body-secondary py-5">
                                    No hay reservas para mostrar en este
                                    período.
                                </td>

                            </tr>

                        @endforelse

                    </tbody>
                </table>

            </div>

        </div>

    </div>

    <div class="card border-0 shadow-sm mb-4">

        <div class="card-header bg-transparent border-0 px-4 pt-4 d-flex flex-wrap align-items-center justify-content-between gap-2">

            <div>

                <h2 class="h5 fw-bold mb-1">Próximas salidas</h2>
                <p class="small text-body-secondary mb-0">Desde ahora y durante los próximos 7 días. Solo empresas y rutas activas.</p>

            </div>

            <a href="{{ route('admin.programaciones.list') }}" class="small" wire:navigate>Ver programaciones</a>

        </div>

        <div class="card-body px-0">

            <div class="table-responsive">

                <table class="table table-hover align-middle mb-0">

                    <thead class="table-light">

                        <tr>
                            <th scope="col" class="ps-4">Ruta</th>

                            <th scope="col">Empresa</th>

                            <th scope="col">Salida</th>

                            <th scope="col" class="pe-4 text-end">Asientos disponibles</th>

                        </tr>
                    </thead>

                    <tbody>

                        @forelse ($proximasSalidas as $salida)

                            <tr wire:key="salida-{{ $salida->id }}">
                                <td class="ps-4">
                                    <a href="{{ route('admin.programaciones.passengers', ['programacion_id' => $salida->id]) }}"
                                        class="fw-semibold text-decoration-none" wire:navigate>
                                        {{ $salida->viaje?->origenTerminal?->nombre }} <i class="bi bi-arrow-right mx-1"
                                            aria-label="hacia"></i> {{ $salida->viaje?->destinoTerminal?->nombre }}
                                    </a>
                                </td>

                                <td>
                                    {{ $salida->viaje?->empresa?->nombre }}
                                </td>

                                <td class="text-nowrap">
                                    {{ $salida->fecha_salida->format('d/m/Y') }} ·
                                    {{ substr($salida->hora_salida, 0, 5) }}
                                </td>

                                <td class="pe-4 text-end">
                                    <span
                                        class="badge {{ $salida->asientos_disponibles > 0 ? 'text-bg-light' : 'text-bg-warning' }}">{{ $salida->asientos_disponibles }}
                                        / {{ $salida->asientos_totales }}</span>
                                </td>

                            </tr>

                        @empty

                            <tr>
                                <td colspan="4" class="text-center text-body-secondary py-5">
                                    No hay salidas activas programadas para
                                    los próximos 7 días.
                                </td>

                            </tr>

                        @endforelse

                    </tbody>
                </table>

            </div>

        </div>

    </div>

</div>
