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

    public const ESTADO_ELIMINADO = 0;

    public const ESTADO_PROGRAMADO = 1;

    public const ESTADO_INACTIVO = 2;

    public const ESTADO_FINALIZADO = 3;

    protected $table = 'programaciones';

    protected $fillable = [
        'viaje_id',
        'transporte_id',
        'asientos_totales',
        'estatus',
    ];

    protected function casts(): array
    {
        return ['asientos_totales' => 'integer', 'estatus' => 'integer'];
    }

    public function viaje(): BelongsTo
    {
        return $this->belongsTo(Viaje::class, 'viaje_id');
    }

    public function transporte(): BelongsTo
    {
        return $this->belongsTo(Transporte::class, 'transporte_id');
    }

    public function tramoPrecios(): HasMany
    {
        return $this->hasMany(ProgramacionTramoPrecio::class, 'programacion_id');
    }

    public function getSalida(): ?\Carbon\Carbon
    {
        return $this->tramoPrecios
            ->where('origen_terminal_id', $this->viaje->origen_terminal_id)
            ->map(function ($tramo) {
                return $tramo->getSalida();
            })->filter()->sort()->first();
    }

    public function getLlegada(): ?\Carbon\Carbon
    {
        return $this->tramoPrecios
            ->where('destino_terminal_id', $this->viaje->destino_terminal_id)
            ->map(function ($tramo) {
                return $tramo->getLlegada();
            })->filter()->sortDesc()->first();
    }

    private static function consultaSalida(string $campo): Builder
    {
        return ProgramacionTramoPrecio::query()
            ->selectRaw($campo === 'fecha_salida' ? 'DATE(programacion_tramo_precios.fecha_salida)' : 'programacion_tramo_precios.hora_salida')
            ->join('viajes', 'viajes.origen_terminal_id', '=', 'programacion_tramo_precios.origen_terminal_id')
            ->whereColumn('viajes.id', 'programaciones.viaje_id')
            ->whereColumn('programacion_tramo_precios.programacion_id', 'programaciones.id')
            ->whereColumn('programacion_tramo_precios.origen_terminal_id', 'viajes.origen_terminal_id')
            ->whereNotNull('programacion_tramo_precios.fecha_salida')
            ->whereNotNull('programacion_tramo_precios.hora_salida')
            ->orderBy('programacion_tramo_precios.fecha_salida')
            ->orderBy('programacion_tramo_precios.hora_salida')
            ->limit(1);
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
        $query = self::query()->select('programaciones.*')->addSelect([
            'salida_fecha' => self::consultaSalida('fecha_salida'),
            'salida_hora' => self::consultaSalida('hora_salida'),
        ])->with([0 => 'viaje.empresa', 1 => 'viaje.origenTerminal', 2 => 'viaje.destinoTerminal', 3 => 'tramoPrecios']);

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
        } else {
            $query->where('programaciones.estatus', '!=', self::ESTADO_DELETE);
        }

        if (! empty($filters['date_from'])) {
            $query->where(self::consultaSalida('fecha_salida'), '>=', self::date($filters['date_from']));
        }

        if (! empty($filters['date_to'])) {
            $query->where(self::consultaSalida('fecha_salida'), '<=', self::date($filters['date_to'], 'date_to'));
        }

        if (! empty($filters['proximas'])) {
            $from = now();
            $query->where(function ($query) use ($from) {
                return $query->where(self::consultaSalida('fecha_salida'), '>', $from->toDateString())->orWhere(function ($query) use ($from) {
                    return $query->where(self::consultaSalida('fecha_salida'), $from->toDateString())->where(self::consultaSalida('hora_salida'), '>=', $from->format('H:i:s'));
                });
            });
        }

        if (isset($filters['transporte_id']) && $filters['transporte_id'] !== '') {
            $query->where('programaciones.transporte_id', $filters['transporte_id']);
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

            self::withBolivaresTotals($query);
        }

        return $query;
    }

    public static function searchDetailViajes(int $viaje_id): Builder
    {
        $query = self::searchAdmin()
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
            );

        self::withBolivaresTotals($query);

        return $query->orderByDesc('salida_fecha')
            ->orderByDesc('salida_hora');
    }

    public static function upcomingForDashboard(string $dateFrom, string $dateTo, ?int $empresaId = null, string $tipoTransporte = ''): Builder
    {
        return self::searchAdmin('', [
            'empresa_id' => $empresaId,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'proximas' => true,
        ])->when($tipoTransporte !== '', function ($query) use ($tipoTransporte) {
            $query->whereHas('transporte', function ($transporte) use ($tipoTransporte) {
                $transporte->where('tipo_transporte', $tipoTransporte);
            });
        })->orderBy('salida_fecha')
            ->orderBy('salida_hora')
            ->orderBy('id');
    }

    public static function paraTaquilla(int $empresaId, bool $soloFuturas = true): Builder
    {
        return self::searchAdmin('', [
            'empresa_id' => $empresaId,
            'status' => self::ESTADO_PROGRAMADO,
            'proximas' => $soloFuturas,
        ])->whereHas('viaje', function ($query) {
            $query->where('estatus', self::ESTADO_ACTIVE)
                ->whereHas('empresa', function ($empresa) {
                    $empresa->where('estatus', self::ESTADO_ACTIVE);
                });
        })->whereHas('transporte', function ($query) {
            $query->where('estatus', self::ESTADO_ACTIVE);
        })->with(['transporte', 'tramoPrecios.origenTerminal', 'tramoPrecios.destinoTerminal'])
            ->orderBy('salida_fecha')->orderBy('salida_hora');
    }

    public static function bloquear(int $programacionId): self
    {
        return self::query()
            ->with(['viaje.tramos', 'viaje.empresa', 'transporte'])
            ->whereKey($programacionId)
            ->lockForUpdate()
            ->firstOrFail();
    }

    private static function withBolivaresTotals(Builder $query): Builder
    {
        return $query->addSelect([
            'ventas_total_bs' => Pasaje::query()
                ->selectRaw('COALESCE(SUM(pasajes.subtotal * tipos_cambios.valor_usd), 0)')
                ->join('reservas', 'reservas.id', '=', 'pasajes.reserva_id')
                ->join('tipos_cambios', 'tipos_cambios.id', '=', 'reservas.tipos_cambios_id')
                ->whereColumn('reservas.programacion_id', 'programaciones.id')
                ->where('reservas.estado_pago', Reserva::ESTADO_PAGO_PAGADO),
            'tasas_servicio_total_bs' => Pasaje::query()
                ->selectRaw('COALESCE(SUM(pasajes.tasa_servicio * tipos_cambios.valor_usd), 0)')
                ->join('reservas', 'reservas.id', '=', 'pasajes.reserva_id')
                ->join('tipos_cambios', 'tipos_cambios.id', '=', 'reservas.tipos_cambios_id')
                ->whereColumn('reservas.programacion_id', 'programaciones.id')
                ->where('reservas.estado_pago', Reserva::ESTADO_PAGO_PAGADO),
        ]);
    }
}
