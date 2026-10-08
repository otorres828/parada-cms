<div class="container-fluid px-0">

    <div class="d-flex justify-content-between align-items-center mb-4">

        <h1 class="h3 mb-0">Pasaje digital</h1>

        @if ($canViewReservation)
            <a
                href="{{ route('empresas.reservas.detail', $pasaje->reserva_id) }}"
                class="btn btn-secondary"
            >
                Volver a la reserva
            </a>
        @endif

    </div>

    <div class="row justify-content-center">

        <div class="col-lg-7">

            <div class="card shadow-sm">

                <div class="card-header bg-white">

                    <div class="d-flex justify-content-between align-items-center">

                        <div>
                            <h2 class="h5 mb-1">Pasaje {{ $pasaje->localizador }}</h2>
                            <span class="text-muted">{{ $pasaje->viajero_nombre_completo }}</span>
                        </div>

                        <span class="badge text-bg-success">Confirmado</span>

                    </div>

                </div>

                <div class="card-body">

                    <div class="row g-4">

                        <div class="col-md-8">

                            <div class="mb-3">

                                <span class="d-block text-muted small">Ruta</span>

                                <strong>
                                    {{ $pasaje->reserva->origenTerminal?->nombre }}
                                    <i class="bi bi-arrow-right mx-1"></i>
                                    {{ $pasaje->reserva->destinoTerminal?->nombre }}
                                </strong>

                            </div>

                            <div class="row g-3">

                                <div class="col-sm-6">

                                    <span class="d-block text-muted small">Salida</span>

                                    <strong>
                                        {{ $pasaje->reserva->tramoPrecio?->getSalida()?->format('d/m/Y h:i A') }}
                                    </strong>

                                </div>

                                <div class="col-sm-6">

                                    <span class="d-block text-muted small">Asiento</span>

                                    <strong>{{ $pasaje->numero_asiento ?? 'Por asignar' }}</strong>

                                </div>

                            </div>

                            <div class="mt-3">

                                <span class="d-block text-muted small">Transporte</span>

                                <strong>
                                    {{ $pasaje->reserva->programacion?->transporte?->nombre }}
                                </strong>

                            </div>

                        </div>

                        <div class="col-md-4 text-center">

                            @if ($qr)
                                <div class="bg-white border rounded p-3 d-inline-block">
                                   <img
                                        src="{{ $qr }}"
                                        alt="Código QR del pasaje"
                                        class="img-fluid"
                                        style="max-width: 180px;"
                                    >
                                </div>

                                <span class="d-block text-muted small mt-2">
                                    Presenta este código al abordar.
                                </span>
                            @endif

                        </div>

                    </div>

                </div>

                <div class="card-footer bg-white">

                    @if ($walletUrl)
                        <a
                            href="{{ $walletUrl }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="btn btn-dark w-100"
                        >
                            <i class="bi bi-google me-2"></i>
                            Añadir a Google Wallet
                        </a>
                    @else
                        <div class="alert alert-warning mb-0" role="alert">
                            {{ $walletError ?? 'No fue posible generar el pase para Google Wallet.' }}
                        </div>
                    @endif

                </div>

            </div>

        </div>

    </div>

</div>