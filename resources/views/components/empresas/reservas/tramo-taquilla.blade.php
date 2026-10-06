{{-- Selección de origen, destino y salida que cubre el tramo. --}}
@props(['origenes', 'destinos', 'opciones', 'cambio'])
<div class="card mb-3">
    <div class="card-header">1. Selecciona el viaje</div>
    <div class="card-body row g-3">
        <div class="col-md-3">
            <label class="form-label" for="taquilla-tramo-fecha">Fecha de salida</label>
            <x-form.date-input
                id="taquilla-tramo-fecha"
                form="registrar-taquilla"
                required
                class="form-control"
                wire:model.live="fecha"
                min="{{ today()->toDateString() }}"
            />
        </div>
        <div class="col-md-3">
            <label class="form-label" for="taquilla-tramo-origen">Origen</label>
            <select id="taquilla-tramo-origen" form="registrar-taquilla" required class="form-select" wire:model.live="origenId">
                <option value="">Seleccionar origen</option>
                @foreach ($origenes as $terminal)
                    <option value="{{ $terminal->id }}">{{ $terminal->nombre }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label" for="taquilla-tramo-destino">Destino</label>
            <select id="taquilla-tramo-destino" form="registrar-taquilla" required class="form-select" wire:model.live="destinoId">
                <option value="">Seleccionar destino</option>
                @foreach ($destinos as $terminal)
                    <option value="{{ $terminal->id }}">{{ $terminal->nombre }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label" for="taquilla-tramo-salida">Salida disponible para el tramo</label>
            <select id="taquilla-tramo-salida" form="registrar-taquilla" required class="form-select" wire:model.live="tarifaId">
                <option value="">Seleccionar salida</option>
                @foreach ($opciones as $opcion)
                    <option value="{{ $opcion->id }}">{{ $opcion->getSalida()?->format('d/m H:i') ?? 'Sin horario' }}
                        · #{{ $opcion->programacion_id }} · USD {{ number_format($opcion->precio, 2) }} - BS {{ number_format($opcion->precio * (float) $cambio?->valor_usd, 2, ',', '.') }}</option>
                @endforeach
            </select>
        </div>

        @if ($origenes->isEmpty())

            <div class="col-12">

                <div class="alert alert-info mb-0" role="status">

                    No hay salidas con tramos disponibles para la fecha seleccionada. Selecciona otra fecha o revisa las programaciones.

                </div>

            </div>

        @endif

    </div>
</div>
