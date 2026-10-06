@props(['trayectos', 'tramos', 'capacidad' => null])

<div class="card">

    <div class="card-header fw-semibold">Trayectos disponibles para vender</div>

    <div class="table-responsive">

        <table class="table align-middle mb-0">

            <thead>
                <tr>
                    <th>Vender</th>
                    <th>Trayecto</th>
                    <th>Salida</th>
                    <th>Llegada</th>
                    <th>Precio base (USD)</th>
                    <th>Puestos a vender</th>
                </tr>
            </thead>

            <tbody>

                @forelse ($trayectos as $trayecto)
                    @php
                        $clave = $trayecto->origen_terminal_id.'-'.$trayecto->destino_terminal_id;
                        $habilitado = $tramos[$clave]['habilitado'] ?? false;
                    @endphp

                    <tr wire:key="programacion-tramo-{{ $trayecto->id }}" class="{{ $habilitado ? '' : 'text-muted' }}">

                        <td>
                            <input type="checkbox" class="form-check-input" wire:model.live="tramos.{{ $clave }}.habilitado" aria-label="Vender {{ $trayecto->origenTerminal?->nombre }} a {{ $trayecto->destinoTerminal?->nombre }}">
                        </td>

                        <td>
                            {{ $trayecto->origenTerminal?->nombre }} → {{ $trayecto->destinoTerminal?->nombre }}
                        </td>

                        <td style="min-width: 170px;">
                            <input id="salida-hora-{{ $clave }}" type="time" class="form-control" wire:model.live="tramos.{{ $clave }}.hora_salida" aria-label="Hora de salida" @disabled(! $habilitado) @required($habilitado)>
                            <small class="d-block text-muted mt-1">{{ !empty($tramos[$clave]['fecha_salida']) ? \Carbon\Carbon::parse($tramos[$clave]['fecha_salida'])->format('d/m/Y') : '—' }}</small>
                        </td>

                        <td style="min-width: 170px;">
                            <input id="llegada-hora-{{ $clave }}" type="time" class="form-control" wire:model.live="tramos.{{ $clave }}.hora_llegada" aria-label="Hora de llegada" @disabled(! $habilitado) @required($habilitado)>
                            <small class="d-block text-muted mt-1">{{ !empty($tramos[$clave]['fecha_llegada']) ? \Carbon\Carbon::parse($tramos[$clave]['fecha_llegada'])->format('d/m/Y') : '—' }}</small>
                        </td>

                        <td style="min-width: 130px;">
                            <input id="precio-{{ $clave }}" type="number" step="0.01" min="0" max="9999999999.99" class="form-control" wire:model="tramos.{{ $clave }}.precio" aria-label="Precio del trayecto" @disabled(! $habilitado) @required($habilitado)>
                        </td>

                        <td style="min-width: 150px;">
                            <input id="puestos-{{ $clave }}" type="number" min="1" max="{{ $capacidad ?? 100 }}"
                                class="form-control" wire:model="tramos.{{ $clave }}.asientos_maximos_permitidos"
                                placeholder="{{ $capacidad ? 'Todos ('.$capacidad.')' : 'Todos' }}" aria-label="Puestos a vender en el trayecto" @disabled(! $habilitado)>
                        </td>

                    </tr>

                @empty
                    <tr>
                        <td colspan="6" class="text-center py-4">Selecciona una ruta con trayectos y precios base.</td>
                    </tr>
                @endforelse

            </tbody>

        </table>

    </div>

</div>
