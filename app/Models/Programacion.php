<?php

namespace App\Models;

use App\Traits\TraitGeneral;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Programacion extends ModelHelper
{
    use TraitGeneral;

    protected $table = 'programaciones';

    protected $fillable = [
        'viaje_id',
        'autobus_id',
        'fecha_salida',
        'hora_salida',
        'asientos_totales',
        'estatus',
    ];

    protected function casts(): array
    {
        return ['fecha_salida' => 'date', 'asientos_totales' => 'integer', 'estatus' => 'integer'];
    }

    public function viaje(): BelongsTo
    {
        return $this->belongsTo(Viaje::class, 'viaje_id');
    }

    public function autobus(): BelongsTo
    {
        return $this->belongsTo(Autobus::class, 'autobus_id');
    }

    public function tramoPrecios(): HasMany
    {
        return $this->hasMany(ProgramacionTramoPrecio::class, 'programacion_id');
    }

    public function reservas(): HasMany
    {
        return $this->hasMany(Reserva::class, 'programacion_id');
    }

    public function pasajes(): HasManyThrough
    {
        return $this->hasManyThrough(Pasaje::class, Reserva::class, 'programacion_id', 'reserva_id');
    }

    public static function searchAdmin(string $search = '', array $filters = []): Builder
    {
        $query = self::query()->with([0 => 'viaje.empresa', 1 => 'viaje.origenTerminal', 2 => 'viaje.destinoTerminal', 3 => 'tramoPrecios']);

        if ($search !== '') {
            $query->where(function ($query) use ($search) {
                $query->where('programaciones.id', ctype_digit($search) ? $search : -1);
                $query->orWhereHas('viaje.empresa', function ($query) use ($search) {
                    return $query->where('nombre', 'like', '%'.$search.'%');
                });
            });
        }

        $status = $filters['status'] ?? null;

        if ($status !== null && $status !== '') {
            $query->where('programaciones.estatus', $status);
        }

        if (! empty($filters['date_from'])) {
            $query->whereDate('programaciones.fecha_salida', '>=', self::date($filters['date_from']));
        }

        if (! empty($filters['date_to'])) {
            $query->whereDate('programaciones.fecha_salida', '<=', self::date($filters['date_to']));
        }

        if (! empty($filters['proximas'])) {
            $from = now();
            $query->where(function ($query) use ($from) {
                return $query->where('fecha_salida', '>', $from->toDateString())->orWhere(function ($query) use ($from) {
                    return $query->where('fecha_salida', $from->toDateString())->where('hora_salida', '>=', $from->format('H:i:s'));
                });
            });
        }

        if (isset($filters['autobus_id']) && $filters['autobus_id'] !== '') {
            $query->where('programaciones.autobus_id', $filters['autobus_id']);
        }

        if (isset($filters['empresa_id']) && $filters['empresa_id'] !== '') {
            $query->whereHas('viaje', function ($query) use ($filters) {
                return $query->where('empresa_id', $filters['empresa_id']);
            });
        }

        if (! empty($filters['historial_ventas'])) {
            $query
                ->withCount([
                    'pasajes as pasajes_vendidos' => function ($query) {
                        return $query->where('reservas.estado_pago', Reserva::ESTADO_PAGO_PAGADO);
                    },
                    'pasajes as pasajes_pendientes' => function ($query) {
                        return $query->where('reservas.estado_pago', Reserva::ESTADO_PAGO_PENDIENTE);
                    },
                    'pasajes as pasajes_cancelados' => function ($query) {
                        return $query->where('reservas.estado_pago', Reserva::ESTADO_PAGO_CANCELADO);
                    },
                    'pasajes as pasajes_reembolsados' => function ($query) {
                        return $query->where('reservas.estado_pago', Reserva::ESTADO_PAGO_REEMBOLSADO);
                    },
                    'pasajes as pasajes_fallidos' => function ($query) {
                        return $query->where('reservas.estado_pago', Reserva::ESTADO_PAGO_FALLIDO);
                    },
                ])
                ->withSum(
                    [
                        'pasajes as ventas_total' => function ($query) {
                            return $query->where('reservas.estado_pago', Reserva::ESTADO_PAGO_PAGADO);
                        },
                    ],
                    'subtotal',
                )
                ->withSum(
                    [
                        'pasajes as tasas_servicio_total' => function ($query) {
                            return $query->where('reservas.estado_pago', Reserva::ESTADO_PAGO_PAGADO);
                        },
                    ],
                    'tasa_servicio',
                );
        }

        return $query;
    }

    public static function searchDetailViajes(int $viaje_id): Builder
    {
        return self::searchAdmin()
            ->where('viaje_id', $viaje_id)
            ->withCount(['pasajes as pasajes_vendidos' => fn ($q) => $q->where('reservas.estado_pago', Reserva::ESTADO_PAGO_PAGADO)])
            ->withCount(['pasajes as pasajes_pendientes' => fn ($q) => $q->where('reservas.estado_pago', Reserva::ESTADO_PAGO_PENDIENTE)])
            ->withCount(['pasajes as pasajes_cancelados' => fn ($q) => $q->where('reservas.estado_pago', Reserva::ESTADO_PAGO_CANCELADO)])
            ->withCount(['pasajes as pasajes_reembolsados' => fn ($q) => $q->where('reservas.estado_pago', Reserva::ESTADO_PAGO_REEMBOLSADO)])
            ->withCount(['pasajes as pasajes_fallidos' => fn ($q) => $q->where('reservas.estado_pago', Reserva::ESTADO_PAGO_FALLIDO)])
            ->withSum(
                [
                    'pasajes as ventas_total' => function ($query) {
                        return $query->where('reservas.estado_pago', Reserva::ESTADO_PAGO_PAGADO);
                    },
                ],
                'subtotal',
            )
            ->withSum(
                [
                    'pasajes as tasas_servicio_total' => function ($query) {
                        return $query->where('reservas.estado_pago', Reserva::ESTADO_PAGO_PAGADO);
                    },
                ],
                'tasa_servicio',
            )
            ->orderByDesc('fecha_salida')
            ->orderByDesc('hora_salida');
    }

    public static function upcomingForDashboard(string $dateFrom, string $dateTo): Builder
    {
        return self::searchAdmin('', [
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'activas' => true,
            'proximas' => true,
        ])->orderBy('fecha_salida')
            ->orderBy('hora_salida')
            ->orderBy('id');
    }
}
