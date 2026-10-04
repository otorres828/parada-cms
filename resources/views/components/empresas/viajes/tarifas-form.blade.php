{{-- Todas las combinaciones del recorrido, cada una con su precio propio. --}}
@props(['terminales', 'combinaciones'])

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span class="fw-semibold">2. Precios base por trayecto</span>
        <span class="badge bg-light text-dark">{{ count($combinaciones) }} combinaciones</span>
    </div>
    <div class="card-body border-bottom small text-muted">Cada precio es independiente. Se utilizará como base al crear una programación; cambiarlo aquí no modifica las salidas existentes.</div>
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr>
                    <th>Origen</th>
                    <th>Destino</th>
                    <th style="min-width: 145px;">Precio base ($)</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($combinaciones as $tramo)
                    <tr wire:key="viaje-precio-{{ $tramo['clave'] }}">
                        <td>{{ $terminales->get($tramo['origen_terminal_id'])?->nombre }}</td>
                        <td>{{ $terminales->get($tramo['destino_terminal_id'])?->nombre }}</td>
                        <td>
                            <input id="precio-{{ $tramo['clave'] }}" type="number" class="form-control form-control-sm" min="0" max="9999999999.99" step="0.01" wire:model="precios.{{ $tramo['clave'] }}" aria-label="Precio del trayecto {{ $tramo['clave'] }}" required>
                            @error('precios.'.$tramo['clave']) <div class="text-danger small">{{ $message }}</div> @enderror
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="text-muted py-4">Añade paradas para generar los trayectos.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
