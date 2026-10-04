@props(['fechasProgramacion', 'programacionId' => null])

<div class="card mb-3">

    <div class="card-header fw-semibold">Fechas de programación</div>

    <div class="card-body">

        <div class="row g-3">

            @if ($programacionId === null)
                <div class="col-md-4">

                    <label for="programacion-modo" class="form-label">Seleccionar fechas</label>
                    <select id="programacion-modo" class="form-select" wire:model.live="modoFechas">
                        <option value="unica">Una fecha</option>
                        <option value="rango">Rango de fechas</option>
                        <option value="especificas">Fechas específicas</option>
                    </select>

                </div>
            @endif

            <div class="col-md-4">

                <label class="form-label" for="programacion-fecha">
                    {{ $this->modoFechas === 'especificas' && $programacionId === null ? 'Fecha de referencia para los horarios' : 'Fecha de salida' }}
                </label>
                <input id="programacion-fecha" type="date" class="form-control" wire:model.live="fechaSalida"
                    min="{{ today()->format('Y-m-d') }}" max="2100-12-31" required>

            </div>

            <div class="col-md-4">

                <label class="form-label" for="programacion-hora">Hora de salida</label>
                <input id="programacion-hora" type="time" class="form-control" wire:model="horaSalida" required>

            </div>

            @if ($programacionId === null && $this->modoFechas === 'rango')
                <div class="col-md-4">

                    <label for="programacion-hasta" class="form-label">Fecha final del rango</label>
                    <input id="programacion-hasta" type="date" class="form-control" wire:model.live="fechaHasta"
                        min="{{ $this->fechaSalida }}" max="2100-12-31" required>

                </div>

                <div class="col-12">

                    <label class="form-label">Días de salida</label>
                    <div class="d-flex flex-wrap gap-3">

                        @foreach ([1 => 'Lunes', 2 => 'Martes', 3 => 'Miércoles', 4 => 'Jueves', 5 => 'Viernes', 6 => 'Sábado', 7 => 'Domingo'] as $dia => $nombre)
                            <div class="form-check">

                                <input id="programacion-dia-{{ $dia }}" class="form-check-input" type="checkbox"
                                    value="{{ $dia }}" wire:model.live="diasSemana">
                                <label for="programacion-dia-{{ $dia }}" class="form-check-label">{{ $nombre }}</label>

                            </div>
                        @endforeach

                    </div>

                </div>
            @elseif ($programacionId === null && $this->modoFechas === 'especificas')
                <div class="col-md-8">

                    <label for="programacion-fecha-especifica" class="form-label">Añadir fecha de salida</label>
                    <div class="d-flex gap-2">

                        <input id="programacion-fecha-especifica" type="date" class="form-control" wire:model="fechaEspecifica"
                            min="{{ today()->format('Y-m-d') }}" max="2100-12-31">
                        <button type="button" class="btn btn-outline-primary text-nowrap" wire:click="agregarFecha" wire:loading.attr="disabled">Añadir fecha</button>

                    </div>

                </div>

                <div class="col-12 d-flex flex-wrap gap-2">

                    @foreach ($this->fechasEspecificas as $indice => $fecha)
                        <button type="button" class="btn btn-outline-secondary btn-sm" wire:key="fecha-programacion-{{ $fecha }}"
                            wire:click="removerFecha({{ $indice }})" aria-label="Remover fecha {{ $fecha }}">
                            {{ \Carbon\Carbon::parse($fecha)->format('d/m/Y') }} <i class="bi bi-x-lg ms-1"></i>
                        </button>
                    @endforeach

                </div>
            @endif

        </div>

        <div class="d-flex flex-wrap align-items-center gap-3 mt-3">

            <button type="button" class="btn btn-outline-primary" wire:click="cargarTramos" wire:loading.attr="disabled" @disabled($this->viajeId === '')>
                Actualizar horarios de los trayectos
            </button>
            <span class="text-muted small">Se calculan según la hora de salida y las duraciones de la ruta. Puedes ajustarlos en cada trayecto.</span>

        </div>

        @if ($programacionId === null)
            <div class="border-top mt-3 pt-3">

                <strong>{{ count($fechasProgramacion) === 1 ? 'Se creará 1 programación.' : 'Se crearán '.count($fechasProgramacion).' programaciones.' }}</strong>
                <div class="d-flex flex-wrap gap-2 mt-2" style="max-height: 160px; overflow-y: auto;">

                    @foreach ($fechasProgramacion as $fecha)
                        <span class="badge bg-light text-dark border">{{ \Carbon\Carbon::parse($fecha)->format('d/m/Y') }}</span>
                    @endforeach

                </div>
                <p class="text-muted small mb-0 mt-2">La fecha inicial del formulario es la referencia de los horarios. Cada fecha seleccionada crea una programación independiente con los mismos precios y horarios relativos. Se permite hasta 366 fechas por lote.</p>

            </div>

        @endif

    </div>

</div>
