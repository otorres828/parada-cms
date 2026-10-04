<?php

namespace App\Models;

use App\Traits\TraitGeneral;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ViajeTramo extends ModelHelper
{
    use TraitGeneral;

    protected $table = 'viaje_tramos';

    protected $fillable = [
        'viaje_id',
        'origen_terminal_id',
        'destino_terminal_id',
        'orden',
        'duracion_estimada',
        'precio',
    ];

    protected function casts(): array
    {
        return [
            'orden' => 'integer',
            'precio' => 'decimal:2',
        ];
    }

    public static function precioBase(Viaje $viaje, int $origenId, int $destinoId): string
    {
        $tramo = $viaje->tramos->first(function ($tramo) use ($origenId, $destinoId) {
            return (int) $tramo->origen_terminal_id === $origenId
                && (int) $tramo->destino_terminal_id === $destinoId;
        });
        self::exigir($tramo !== null, 'tramo', 'El trayecto no está configurado en esta ruta.');
        self::exigir($tramo->precio !== null && bccomp($tramo->precio, '0', 2) >= 0, 'precio', 'Configura un precio base válido para el trayecto.');

        return $tramo->precio;
    }

    public function viaje(): BelongsTo
    {
        return $this->belongsTo(Viaje::class, 'viaje_id');
    }

    public function origenTerminal(): BelongsTo
    {
        return $this->belongsTo(Terminal::class, 'origen_terminal_id');
    }

    public function destinoTerminal(): BelongsTo
    {
        return $this->belongsTo(Terminal::class, 'destino_terminal_id');
    }

    public static function disponibilidadPorTramos(Collection $programaciones): array
    {
        if ($programaciones->isEmpty()) {
            return [];
        }

        $programaciones->loadMissing([
            'viaje.tramos',
            'transporte',
            'tramoPrecios.origenTerminal',
            'tramoPrecios.destinoTerminal',
        ]);

        $reservas = Reserva::reservasQueBloqueanAsientos()
            ->whereIn('programacion_id', $programaciones->modelKeys())
            ->with('pasajes')
            ->get()
            ->groupBy('programacion_id');
        $resultado = [];

        foreach ($programaciones as $programacion) {
            $terminales = Terminal::obtenerSecuenciaRuta($programacion);
            $resultado[$programacion->id] = [];

            foreach ($programacion->tramoPrecios as $tarifa) {
                $resultado[$programacion->id][$tarifa->id] = Pasaje::disponibilidad(
                    $programacion,
                    $tarifa,
                    $terminales,
                    null,
                    $reservas->get($programacion->id, new Collection),
                ) + [
                    'origen' => $tarifa->origenTerminal?->nombre ?? '—',
                    'destino' => $tarifa->destinoTerminal?->nombre ?? '—',
                ];
            }
        }

        return $resultado;
    }
}
