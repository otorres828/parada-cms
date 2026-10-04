<?php

namespace App\Models;

use App\Traits\TraitGeneral;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Viaje extends ModelHelper
{
    use TraitGeneral;

    protected $table = 'viajes';

    protected $fillable = [
        'empresa_id',
        'origen_terminal_id',
        'destino_terminal_id',
        'duracion_estimada',
        'comentario',
        'estatus',
    ];

    protected function casts(): array
    {
        return ['estatus' => 'integer'];
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function origenTerminal(): BelongsTo
    {
        return $this->belongsTo(Terminal::class, 'origen_terminal_id');
    }

    public function destinoTerminal(): BelongsTo
    {
        return $this->belongsTo(Terminal::class, 'destino_terminal_id');
    }

    public function tramos(): HasMany
    {
        return $this->hasMany(ViajeTramo::class, 'viaje_id')->orderBy('orden');
    }

    public function tramosConsecutivos(): \Illuminate\Database\Eloquent\Collection
    {
        $terminales = $this->secuenciaTerminales();
        $tramos = new \Illuminate\Database\Eloquent\Collection;
        for ($i = 0; $i < count($terminales) - 1; $i++) {
            $tramo = $this->tramos->first(function ($tramo) use ($terminales, $i) {
                return (int) $tramo->origen_terminal_id === $terminales[$i]
                    && (int) $tramo->destino_terminal_id === $terminales[$i + 1];
            });
            self::exigir($tramo !== null || $this->tramos->isEmpty(), 'viaje', 'Falta un tramo consecutivo de la ruta.');
            if ($tramo !== null) {
                $tramos->push($tramo);
            }
        }

        return $tramos;
    }

    public function secuenciaTerminales(): array
    {
        $terminales = [(int) $this->origen_terminal_id];
        foreach ($this->tramos->sortBy('orden') as $tramo) {
            if ((int) $tramo->origen_terminal_id === (int) $this->origen_terminal_id) {
                $terminales[] = (int) $tramo->destino_terminal_id;
            }
        }
        if ($this->tramos->isEmpty()) {
            $terminales[] = (int) $this->destino_terminal_id;
        }
        self::exigir(
            end($terminales) === (int) $this->destino_terminal_id
                && count($terminales) === count(array_unique($terminales)),
            'viaje',
            'El recorrido debe tener terminales distintos y un destino final coherente.',
        );

        return $terminales;
    }

    public static function combinaciones(array $paradas): array
    {
        $combinaciones = [];
        for ($origen = 0; $origen < count($paradas) - 1; $origen++) {
            for ($destino = $origen + 1; $destino < count($paradas); $destino++) {
                $combinaciones[] = [
                    'clave' => $paradas[$origen].'-'.$paradas[$destino],
                    'origen_terminal_id' => (int) $paradas[$origen],
                    'destino_terminal_id' => (int) $paradas[$destino],
                ];
            }
        }

        return $combinaciones;
    }

    public function programaciones(): HasMany
    {
        return $this->hasMany(Programacion::class, 'viaje_id');
    }

    public function reservas(): HasManyThrough
    {
        return $this->hasManyThrough(Reserva::class, Programacion::class, 'viaje_id', 'programacion_id');
    }

    public static function searchAdmin(string $search = '', array $filters = []): Builder
    {
        $query = self::query()->with([0 => 'empresa', 1 => 'origenTerminal', 2 => 'destinoTerminal']);

        if ($search !== '') {
            $query->where(function ($query) use ($search) {
                $query->where('viajes.id', ctype_digit($search) ? $search : -1);
                $query->orWhereHas('empresa', function ($query) use ($search) {
                    return $query->where('nombre', 'like', '%'.$search.'%');
                });
            });
        }

        $status = $filters['status'] ?? ($filters['estatus'] ?? ($filters['estado_pago'] ?? null));

        if ($status !== null && $status !== '') {
            $query->where('viajes.estatus', $status);
        } else {
            $query->where('viajes.estatus', '!=', self::ESTADO_DELETE);
        }

        if (isset($filters['empresa_id']) && $filters['empresa_id'] !== '') {
            $query->where('viajes.empresa_id', $filters['empresa_id']);
        }

        if (! empty($filters['date_from'])) {
            $query->whereDate('viajes.created_at', '>=', self::date($filters['date_from']));
        }

        if (! empty($filters['date_to'])) {
            $query->whereDate('viajes.created_at', '<=', self::date($filters['date_to'], 'date_to'));
        }

        if (! empty($filters['con_tasas'])) {
            $query->withSum(['reservas as tasas_servicio_total' => function ($query) {
                return $query->where('estado_pago', Reserva::ESTADO_PAGO_PAGADO);
            }], 'tasa_servicio');
        }

        return $query;
    }
}
