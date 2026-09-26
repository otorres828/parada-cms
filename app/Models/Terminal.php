<?php

namespace App\Models;

use App\Traits\TraitGeneral;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Terminal extends ModelHelper
{
    use TraitGeneral;

    protected $table = 'terminales';

    protected $fillable = [
        'estado_id',
        'nombre',
        'direccion',
        'latitud',
        'longitud',
        'estatus',
    ];

    protected function casts(): array
    {
        return ['latitud' => 'decimal:7', 'longitud' => 'decimal:7', 'estatus' => 'integer'];
    }

    public function estado(): BelongsTo
    {
        return $this->belongsTo(Estado::class, 'estado_id');
    }

    public function viajesOrigen(): HasMany
    {
        return $this->hasMany(Viaje::class, 'origen_terminal_id');
    }

    public function viajesDestino(): HasMany
    {
        return $this->hasMany(Viaje::class, 'destino_terminal_id');
    }

    public static function searchAdmin(string $search = '', array $filters = []): Builder
    {
        $query = self::query()->with([0 => 'estado']);

        if ($search !== '') {
            $query->where(function ($query) use ($search) {
                $query->where('terminales.id', ctype_digit($search) ? $search : -1);
                $query->orWhere('terminales.nombre', 'like', '%'.$search.'%');
                $query->orWhere('terminales.direccion', 'like', '%'.$search.'%');
            });
        }

        $status = $filters['status'] ?? $filters['estatus'] ?? $filters['estado_pago'] ?? null;

        if ($status !== null && $status !== '') {
            $query->where('terminales.estatus', $status);
        } else {
            $query->where('terminales.estatus', '!=', self::ESTADO_DELETE);
        }

        if (isset($filters['estado_id']) && $filters['estado_id'] !== '') {
            $query->where('terminales.estado_id', $filters['estado_id']);
        }

        if (! empty($filters['date_from'])) {
            $query->whereDate('terminales.created_at', '>=', self::date($filters['date_from']));
        }

        if (! empty($filters['date_to'])) {
            $query->whereDate('terminales.created_at', '<=', self::date($filters['date_to']));
        }

        return $query;
    }

    public static function obtenerSecuenciaRuta(Programacion $programacion): array
    {
        $viaje = $programacion->viaje;
        self::exigir($viaje !== null, 'viaje', 'La ruta no existe.');

        $terminales = [(int) $viaje->origen_terminal_id];
        $orden = 0;

        foreach ($viaje->tramos as $tramo) {
            self::exigir(
                (int) $tramo->origen_terminal_id === end($terminales) && $tramo->orden > $orden,
                'viaje',
                'La secuencia de tramos de la ruta es inválida.',
            );
            $terminales[] = (int) $tramo->destino_terminal_id;
            $orden = $tramo->orden;
        }

        if ($viaje->tramos->isEmpty()) {
            $terminales[] = (int) $viaje->destino_terminal_id;
        }

        self::exigir(
            end($terminales) === (int) $viaje->destino_terminal_id
                && count($terminales) === count(array_unique($terminales)),
            'viaje',
            'La ruta debe tener terminales distintos y un destino final coherente.',
        );

        return $terminales;
    }

    public static function obtenerIntervalo(array $terminales, int $origen, int $destino): array
    {
        $inicio = array_search($origen, $terminales, true);
        $fin = array_search($destino, $terminales, true);

        self::exigir(
            $inicio !== false && $fin !== false && $inicio < $fin,
            'trayecto',
            'El origen y destino no forman un trayecto válido de esta ruta.',
        );

        return [$inicio, $fin];
    }

    public static function validarSalida(Programacion $programacion, int $origen, array $terminales): void
    {
        self::exigir(
            $programacion->estatus === Programacion::ESTADO_PROGRAMADO
                && $programacion->viaje->estatus
                && $programacion->viaje->empresa?->estatus
                && ! $programacion->viaje->empresa?->estaBloqueadaPorCobranza()
                && $programacion->autobus?->estatus
                && ! $programacion->autobus->es_plantilla
                && (int) $programacion->autobus->empresa_id === (int) $programacion->viaje->empresa_id,
            'programacion',
            'La salida no está habilitada para venta.',
        );

        $posicion = array_search($origen, $terminales, true);
        self::exigir($posicion !== false, 'trayecto', 'El origen no pertenece a la ruta.');

        $salida = Carbon::parse($programacion->fecha_salida->format('Y-m-d').' '.$programacion->hora_salida);

        foreach ($programacion->viaje->tramos->take($posicion) as $tramo) {
            self::exigir(
                $tramo->duracion_estimada !== null,
                'programacion',
                'Falta la duración para calcular el embarque intermedio.',
            );
            [$horas, $minutos, $segundos] = array_map('intval', explode(':', $tramo->duracion_estimada));
            $salida->addSeconds($horas * 3600 + $minutos * 60 + $segundos);
        }

        self::exigir(
            $salida->isFuture(),
            'programacion',
            'La hora estimada de salida desde este terminal ya pasó.',
        );
    }
}
