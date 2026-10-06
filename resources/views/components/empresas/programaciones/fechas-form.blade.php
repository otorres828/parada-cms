@props(['fechasProgramacion', 'programacionId' => null])

<div class="card mb-3">

    <div class="card-header fw-semibold">Fechas de programación</div>

    <div class="card-body">

        <div class="d-flex flex-wrap align-items-end gap-3">

            @if ($programacionId === null)
                <div style="width: 240px; max-width: 100%;">

                    <label for="programacion-modo" class="form-label">Seleccionar fechas</label>
                    <select id="programacion-modo" class="form-select" wire:model.live="modoFechas">
                        <option value="unica">Una fecha</option>
                        <option value="rango">Rango de fechas</option>
                        <option value="especificas">Fechas específicas</option>
                    </select>

                </div>
            @endif

            <div style="width: 240px; max-width: 100%;">

                <label class="form-label" for="programacion-fecha">
                    {{ $this->modoFechas === 'especificas' && $programacionId === null ? 'Fecha de referencia' : 'Fecha de salida' }}
                </label>
                <x-form.date-input
                    id="programacion-fecha"
                    class="form-control"
                    wire:model.live="fechaSalida"
                    min="{{ today()->format('Y-m-d') }}"
                    max="2100-12-31"
                    required
                />

            </div>

            <div style="width: 240px; max-width: 100%;">

                <label class="form-label" for="programacion-hora">Hora de salida referencial</label>
                <input id="programacion-hora" type="time" class="form-control" wire:model="horaSalida" required>

            </div>

            <div>

                <button type="button" class="btn btn-outline-primary" wire:click="cargarTramos" wire:loading.attr="disabled" @disabled($this->viajeId === '')>
                    <i class="bi bi-arrow-clockwise me-1" aria-hidden="true"></i> Actualizar horarios
                </button>

            </div>

            @if ($programacionId === null && $this->modoFechas === 'rango')
                <div style="width: 240px; max-width: 100%;">

                    <label for="programacion-hasta" class="form-label">Fecha final del rango</label>
                    <x-form.date-input
                        id="programacion-hasta"
                        class="form-control"
                        wire:model.live="fechaHasta"
                        min="{{ $this->fechaSalida }}"
                        max="2100-12-31"
                        required
                    />

                </div>

                <div class="w-100">

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
                <div style="width: 360px; max-width: 100%;">

                    <label for="programacion-fecha-especifica" class="form-label">Añadir fecha de salida</label>
                    <div class="d-flex gap-2">

                        <x-form.date-input
                            id="programacion-fecha-especifica"
                            class="form-control"
                            wire:model="fechaEspecifica"
                            min="{{ today()->format('Y-m-d') }}"
                            max="2100-12-31"
                        />
                        <button type="button" class="btn btn-outline-primary text-nowrap" wire:click="agregarFecha" wire:loading.attr="disabled">Añadir fecha</button>

                    </div>

                </div>

                <div class="w-100 d-flex flex-wrap gap-2">

                    @foreach ($this->fechasEspecificas as $indice => $fecha)
                        <button type="button" class="btn btn-outline-secondary btn-sm" wire:key="fecha-programacion-{{ $fecha }}"
                            wire:click="removerFecha({{ $indice }})" aria-label="Remover fecha {{ $fecha }}">
                            {{ \Carbon\Carbon::parse($fecha)->format('d/m/Y') }} <i class="bi bi-x-lg ms-1"></i>
                        </button>
                    @endforeach

                </div>
            @endif

        </div>

        <p class="text-muted small mb-0 mt-3">La hora referencial permite calcular los horarios de los trayectos según la duración de la ruta. Puedes ajustarlos en la tabla inferior.</p>

        @if ($programacionId === null)
            <div class="bg-light rounded border p-3 mt-3">

                <strong>{{ count($fechasProgramacion) === 1 ? 'Se creará 1 programación.' : 'Se crearán '.count($fechasProgramacion).' programaciones.' }}</strong>
                <div class="d-flex flex-wrap gap-2 mt-2" style="max-height: 160px; overflow-y: auto;">

                    @foreach ($fechasProgramacion as $fecha)
                        <span class="badge bg-white text-dark border fw-normal px-2 py-2">{{ \Carbon\Carbon::parse($fecha)->format('d/m/Y') }}</span>
                    @endforeach

                </div>
                <p class="text-muted small mb-0 mt-2">Cada fecha tendrá su propia programación. Máximo 366 fechas por lote.</p>

            </div>

        @endif

    </div>

</div>
