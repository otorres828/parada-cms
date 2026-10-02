{{--
    RESUMEN DE LA EMPRESA
    --------------------------------------------------------------------------
    Presenta el resumen de ventas, tasas de servicio y reservas de la empresa autenticada. Incluye
    estados de las compras, últimas reservas y próximas salidas para el período seleccionado.

    Componentes reutilizables utilizados:
    - <x-list.disponibilidad-tramos />: Asientos disponibles por origen y destino según el tope configurado.
    --------------------------------------------------------------------------
--}}

@section('title', 'Resumen de la empresa')

<div class="container-fluid py-4">

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">

        <div>

            <div class="text-primary small fw-semibold text-uppercase mb-1">
                Parada · Empresas
            </div>

            <h1 class="h3 fw-bold mb-1">Resumen de la empresa</h1>
            <p class="text-body-secondary mb-0">Supervisa tu operación y la venta de pasajes.</p>

        </div>


    </div>

    <div class="card border-0 shadow-sm mb-4">

        <div class="card-body d-flex flex-wrap align-items-end justify-content-between gap-3">

            <div>

                <h2 class="h6 fw-bold mb-1">Ventas del período</h2>

                <div class="text-body-secondary small">

                    {{ $desde->format('d/m/Y') }} — {{ $hasta->format('d/m/Y') }}
                    <span wire:loading class="ms-2 text-primary" role="status">Actualizando…</span>

                </div>

            </div>

            <div class="row g-2 align-items-end ms-auto">


                <div class="col-12 col-sm-auto">

                    <label for="dashboard-periodo" class="form-label small text-body-secondary mb-1">
                        Período
                    </label>

                    <select id="dashboard-periodo" class="form-select form-select-sm" wire:model.live="periodo">

                        <option value="hoy">Hoy</option>
                        <option value="7">Últimos 7 días</option>
                        <option value="30">Últimos 30 días</option>
                        <option value="mes">Este mes</option>
                        <option value="personalizado">Personalizado</option>

                    </select>

                </div>

                <div class="col-6 col-sm-auto">

                    <label for="dashboard-date-from" class="form-label small text-body-secondary mb-1">
                        Desde
                    </label>
                    <input id="dashboard-date-from" type="date" class="form-control form-control-sm"
                        wire:model.live="date_from" min="{{ $this->getMinFilterDate() }}"
                        max="{{ $this->getMaxFilterDate() }}">

                </div>

                <div class="col-6 col-sm-auto">

                    <label for="dashboard-date-to" class="form-label small text-body-secondary mb-1">
                        Hasta
                    </label>
                    <input id="dashboard-date-to" type="date" class="form-control form-control-sm"
                        wire:model.live="date_to" min="{{ $this->getMinFilterDate() }}"
                        max="{{ $this->getMaxFilterDate() }}">

                </div>

                <div class="col-auto">

                    <button type="button" class="btn btn-outline-secondary btn-sm" wire:click="$refresh"
                        wire:loading.attr="disabled" aria-label="Actualizar indicadores">
                        <i class="bi bi-arrow-clockwise" aria-hidden="true"></i>
                    </button>

                </div>

            </div>

        </div>

    </div>

    <div class="row g-3 mb-2" wire:loading.class="opacity-50">

        @foreach ([['label' => 'Ventas pagadas', 'value' => number_format($metrics['ventas'], 2, ',', '.'), 'note' => $metrics['reservas_pagadas'] . ' reservas pagadas', 'icon' => 'cash-stack', 'color' => 'primary'], ['label' => 'Pasajes vendidos', 'value' => number_format($metrics['pasajes'], 0, ',', '.'), 'note' => 'Pasajes de reservas pagadas', 'icon' => 'ticket-perforated', 'color' => 'success'], ['label' => 'Tasas de servicio', 'value' => number_format($metrics['tasas'], 2, ',', '.'), 'note' => 'Incluidas en las ventas pagadas', 'icon' => 'receipt', 'color' => 'info'], ['label' => 'Reservas pendientes', 'value' => number_format($metrics['pendientes'], 0, ',', '.'), 'note' => 'Con estado de pago pendiente', 'icon' => 'hourglass-split', 'color' => 'warning']] as $card)
            @if ($card['icon'] !== 'receipt' || $ellosReciben)
            <div class="col-sm-6 {{ $ellosReciben ? 'col-xl-3' : 'col-xl-4' }}">

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
            @endif
        @endforeach

    </div>

    <p class="small text-body-secondary mb-4">Importes en la moneda de operación. Calculados por fecha de compra y
        estado actual de la
        reserva; excluyen cancelaciones y reembolsos. @if ($ellosReciben) Las tasas corresponden al importe que debes transferir a la plataforma. @else Las ventas excluyen las tasas de servicio. @endif</p>

    <div class="row g-3 mb-4">

        <div class="col-12">

            <div class="card h-100 border-0 shadow-sm">

                <div class="card-header bg-transparent border-0 pt-4 px-4">

                    <h2 class="h5 fw-bold mb-1">Estado de las reservas</h2>
                    <p class="small text-body-secondary mb-0">{{ $totalReservas }} reservas en el período seleccionado
                    </p>

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
                                        <span class="fw-semibold">{{ $datos['cantidad'] }} <span
                                                class="text-body-secondary fw-normal">·
                                                {{ $datos['porcentaje'] }}%</span></span>

                                    </div>

                                    <div class="progress" style="height: 6px" role="progressbar"
                                        aria-label="{{ $datos['label'] }}"
                                        aria-valuenow="{{ $datos['porcentaje'] }}" aria-valuemin="0"
                                        aria-valuemax="100">

                                        <div class="progress-bar bg-{{ $datos['color'] }}"
                                            style="width: {{ $datos['porcentaje'] }}%">

                                        </div>

                                    </div>

                                </div>
                            @endforeach

                        </div>

                    @endif

                </div>

            </div>

        </div>

    </div>

    <div class="card border-0 shadow-sm mb-4">

        <div class="card-header bg-transparent border-0 px-4 pt-4">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                <h2 class="h5 fw-bold mb-0">Reservas recientes del período</h2>
                @if ($canListReservas)
                    <a href="{{ route('empresas.reservas.list') }}" class="small ms-auto" wire:navigate>Ver todas las reservas</a>
                @endif
            </div>
        </div>

        <div class="card-body px-0">

            <div class="table-responsive">

                <table class="table table-hover align-middle mb-0">

                    <thead class="table-light">

                        <tr>
                            <th scope="col" class="ps-4">Referencia / fecha</th>

                            <th scope="col">Cliente</th>


                            <th scope="col" class="text-center">Pasajes</th>

                            <th scope="col" class="text-end">Importe</th>

                            <th scope="col" class="pe-4">Estado de pago</th>

                        </tr>
                    </thead>

                    <tbody>

                        @forelse ($ultimasReservas as $reserva)
                            <tr wire:key="reserva-{{ $reserva->id }}">
                                <td class="ps-4">
                                    <span class="fw-semibold">{{ $reserva->codigo_referencia }}</span>

                                    <div class="small text-body-secondary">
                                        {{ $reserva->fecha_compra->format('d/m/Y H:i') }}
                                    </div>
                                </td>

                                <td>
                                    {{ $reserva->usuario?->name ?? 'Sin cliente' }}
                                </td>


                                <td class="text-center">
                                    {{ $reserva->pasajes->count() }}
                                </td>

                                <td class="text-end text-nowrap">
                                    {{ number_format($ellosReciben ? $reserva->monto_total : $reserva->getMontoSinTasa(), 2, ',', '.') }}
                                </td>

                                <td class="pe-4">
                                    <span
                                        class="badge text-bg-{{ $estados[$reserva->estado_pago]['color'] ?? 'secondary' }}">{{ ucfirst($reserva->getStatusPago()) }}</span>
                                </td>

                            </tr>

                        @empty

                            <tr>
                                <td colspan="5" class="text-center text-body-secondary py-5">
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

        <div class="card-header bg-transparent border-0 px-4 pt-4">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-1">
                <h2 class="h5 fw-bold mb-0">Próximas salidas</h2>
                @if ($canListProgramaciones)
                    <a href="{{ route('empresas.programaciones.list') }}" class="small ms-auto" wire:navigate>Ver programaciones</a>
                @endif
            </div>
            <p class="small text-body-secondary mb-0">Desde ahora y durante los próximos 7 días. Solo empresas y
                rutas activas.</p>
        </div>

        <div class="card-body px-0">

            <div class="table-responsive">

                <table class="table table-hover align-middle mb-0">

                    <thead class="table-light">

                        <tr>
                            <th scope="col" class="ps-4">Ruta</th>


                            <th scope="col">Salida</th>

                            <th scope="col" class="pe-4 text-end">Disponibilidad por tramo</th>

                        </tr>
                    </thead>

                    <tbody>

                        @forelse ($proximasSalidas as $salida)
                            <tr wire:key="salida-{{ $salida->id }}">
                                <td class="ps-4">
                                    <span class="fw-semibold">
                                        {{ $salida->viaje?->origenTerminal?->nombre }} <i
                                            class="bi bi-arrow-right mx-1"
                                            aria-label="hacia"></i> {{ $salida->viaje?->destinoTerminal?->nombre }}
                                    </span>
                                </td>


                                <td class="text-nowrap">
                                    {{ $salida->fecha_salida->format('d/m/Y') }} ·
                                    {{ substr($salida->hora_salida, 0, 5) }}
                                </td>

                                <td class="pe-4 text-end">
                                    <x-list.disponibilidad-tramos :tramos="$disponibilidadTramos[$salida->id] ?? []" />
                                </td>

                            </tr>

                        @empty

                            <tr>
                                <td colspan="3" class="text-center text-body-secondary py-5">
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
