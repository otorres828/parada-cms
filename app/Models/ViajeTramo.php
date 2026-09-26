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
    ];

    protected function casts(): array
    {
        return [
            'orden' => 'integer',
        ];
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
            'autobus',
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
