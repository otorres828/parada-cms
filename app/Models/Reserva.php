<?php

namespace App\Models;

use App\Traits\TraitGeneral;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Collection as SupportCollection;

class Reserva extends ModelHelper
{
    use TraitGeneral;

    protected $table = 'reservas';

    protected $fillable = [
        'usuario_id',
        'programacion_id',
        'origen_terminal_id',
        'destino_terminal_id',
        'programacion_tramo_precio_id',
        'cupon_id',
        'reprogramacion_id',
        'codigo_referencia',
        'monto_pasajes',
        'descuento_aplicado',
        'exoneracion_tasa_json',
        'tasa_servicio',
        'monto_total',
        'estado_pago',
        'fecha_compra',
        'fecha_pago',
        'fecha_expiracion',
        'comentarios_auditoria',
    ];

    const ESTADO_PAGO_NUEVO = 1;

    const ESTADO_PAGO_PAGADO = 2;

    const ESTADO_PAGO_PENDIENTE = 3;

    const ESTADO_PAGO_CANCELADO = 4;

    const ESTADO_PAGO_REPROGRAMADO = 5; // RESERVA CANCELADA POR REPROGRAMACION

    const ESTADO_PAGO_REEMBOLSADO = 6;

    const ESTADO_PAGO_FALLIDO = 7;

    protected function casts(): array
    {
        return ['reprogramacion_id' => 'integer', 'estado_pago' => 'integer', 'monto_pasajes' => 'decimal:2', 'descuento_aplicado' => 'decimal:2', 'exoneracion_tasa_json' => 'array', 'tasa_servicio' => 'decimal:2', 'monto_total' => 'decimal:2', 'fecha_compra' => 'datetime', 'fecha_pago' => 'datetime', 'fecha_expiracion' => 'datetime', 'comentarios_auditoria' => 'array'];
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function programacion(): BelongsTo
    {
        return $this->belongsTo(Programacion::class, 'programacion_id');
    }

    public function origenTerminal(): BelongsTo
    {
        return $this->belongsTo(Terminal::class, 'origen_terminal_id');
    }

    public function destinoTerminal(): BelongsTo
    {
        return $this->belongsTo(Terminal::class, 'destino_terminal_id');
    }

    public function tramoPrecio(): BelongsTo
    {
        return $this->belongsTo(ProgramacionTramoPrecio::class, 'programacion_tramo_precio_id');
    }

    public function cupon(): BelongsTo
    {
        return $this->belongsTo(Cupon::class, 'cupon_id');
    }

    public function reservaOriginal(): BelongsTo
    {
        return $this->belongsTo(self::class, 'reprogramacion_id');
    }

    public function reservasReprogramadas(): HasMany
    {
        return $this->hasMany(self::class, 'reprogramacion_id');
    }

    public function reprogramado(): HasOne
    {
        return $this->hasOne(self::class, 'reprogramacion_id');
    }

    public function esReprogramacion(): bool
    {
        return $this->reprogramacion_id !== null;
    }

    public function tieneReprogramacionActiva(): bool
    {
        return $this->reservasReprogramadas()
            ->where(function ($query) {
                $query->whereIn('estado_pago', [
                    self::ESTADO_PAGO_PENDIENTE,
                    self::ESTADO_PAGO_PAGADO,
                ])->orWhere(function ($query) {
                    $query->where('estado_pago', self::ESTADO_PAGO_NUEVO)
                        ->where('fecha_expiracion', '>', now());
                });
            })
            ->exists();
    }

    public function pasajes(): HasMany
    {
        return $this->hasMany(Pasaje::class, 'reserva_id');
    }

    public function detalle(): self
    {
        return $this->load([
            'pasajes',
            'pasajes.viajero',
            'origenTerminal',
            'destinoTerminal',
        ]);
    }

    public function validarVigente(): void
    {
        if ($this->estado_pago !== self::ESTADO_PAGO_NUEVO) {
            return;
        }

        self::exigir(
            $this->fecha_expiracion !== null && $this->fecha_expiracion->isFuture(),
            'reserva',
            'La reserva venció. Selecciona nuevamente los asientos.',
        );
    }

    public function validarEditable(): void
    {
        $this->validarVigente();
        
        self::exigir(
            $this->estado_pago === self::ESTADO_PAGO_NUEVO && ! $this->pago()->exists(),
            'reserva',
            'Solo se puede editar una reserva nueva y sin cobros registrados.',
        );
    }

    public function getStatusPago(): string
    {
        // Limpiamos el valor quitando espacios y forzando a entero
        $estado = is_numeric($this->estado_pago) ? (int) trim($this->estado_pago) : $this->estado_pago;

        return match ($estado) {
            self::ESTADO_PAGO_NUEVO => 'nuevo',
            self::ESTADO_PAGO_PAGADO => 'pagado',
            self::ESTADO_PAGO_PENDIENTE => 'pendiente',
            self::ESTADO_PAGO_CANCELADO => 'cancelado',
            self::ESTADO_PAGO_REPROGRAMADO => 'reprogramado',
            self::ESTADO_PAGO_REEMBOLSADO => 'reembolsado',
            self::ESTADO_PAGO_FALLIDO => 'fallido',
            default => 'desconocido',
        };
    }

    public static function searchAdmin(string $search = '', array $filters = []): Builder
    {
        $query = self::query()->with(['usuario', 'programacion.viaje.empresa', 'pasajes.viajero', 'origenTerminal', 'destinoTerminal']);

        if ($search !== '') {
            $query->where(function ($query) use ($search) {
                $query->where('reservas.id', ctype_digit($search) ? $search : -1);
                $query->orWhere('reservas.codigo_referencia', 'like', '%'.$search.'%');
            });
        }

        $status = $filters['status'] ?? $filters['estatus'] ?? $filters['estado_pago'] ?? null;

        if ($status !== null && $status !== '') {
            if ($status == self::ESTADO_PAGO_PAGADO) {
                // Si el estado es "pagado", incluimos también los estados "reembolsado" y "reprogramado"
                // Esto se debe a que la tasa de servicio es no reembolsable, por lo que se considera que la reserva ha sido pagada en términos de la plataforma.
                $query->whereIn('reservas.estado_pago', [self::ESTADO_PAGO_PAGADO, self::ESTADO_PAGO_REEMBOLSADO, self::ESTADO_PAGO_REPROGRAMADO]);
            } else {
                $query->where('reservas.estado_pago', $status);
            }
        }

        if (! empty($filters['date_from'])) {
            $query->whereDate('reservas.fecha_compra', '>=', self::date($filters['date_from']));
        }

        if (! empty($filters['date_to'])) {
            $query->whereDate('reservas.fecha_compra', '<=', self::date($filters['date_to']));
        }

        if (! empty($filters['empresa_id'])) {
            $query->whereHas('programacion.viaje', function ($query) use ($filters) {
                return $query->where('empresa_id', $filters['empresa_id']);
            });
        }

        return $query;
    }

    public static function searchDetailClient(int $user_id): Builder
    {
        return self::query()->with([0 => 'programacion.viaje.empresa', 1 => 'pasajes.viajero'])
            ->where('usuario_id', $user_id)
            ->whereIn('estado_pago', [self::ESTADO_PAGO_PAGADO, self::ESTADO_PAGO_PENDIENTE])
            ->orderByDesc('fecha_compra');
    }

    public static function salesReport(string $dateFrom, string $dateTo): Builder
    {
        return self::query()
            ->where('estado_pago', self::ESTADO_PAGO_PAGADO)
            ->whereDate('fecha_compra', '>=', self::date($dateFrom))
            ->whereDate('fecha_compra', '<=', self::date($dateTo))
            ->selectRaw('DATE(fecha_compra) as fecha, COUNT(*) as cantidad, SUM(monto_total) as total, SUM(tasa_servicio) as tasas')
            ->groupByRaw('DATE(fecha_compra)')
            ->orderByDesc('fecha');
    }

    public static function companiesReport(string $dateFrom, string $dateTo): Builder
    {
        return self::query()
            ->where('reservas.estado_pago', self::ESTADO_PAGO_PAGADO)
            ->whereDate('reservas.fecha_compra', '>=', self::date($dateFrom))
            ->whereDate('reservas.fecha_compra', '<=', self::date($dateTo))
            ->join('programaciones', 'programaciones.id', '=', 'reservas.programacion_id')
            ->join('viajes', 'viajes.id', '=', 'programaciones.viaje_id')
            ->join('empresas', 'empresas.id', '=', 'viajes.empresa_id')
            ->selectRaw('empresas.id, empresas.nombre, COUNT(*) as cantidad, SUM(reservas.monto_total) as total, SUM(reservas.tasa_servicio) as tasas')
            ->groupBy('empresas.id', 'empresas.nombre')
            ->orderByDesc('total');
    }

    public static function findAdminDetail(int $reservaId): self
    {
        return self::searchAdmin()
            ->with([
                'pasajes.reserva',
                'cupon.configuracionCupon',
                'reservaOriginal',
                'reprogramado',
            ])
            ->findOrFail($reservaId);
    }

    public static function dashboardSummary(array $filters): self
    {
        return self::searchAdmin('', $filters)
            ->selectRaw('COUNT(*) as cantidad, COALESCE(SUM(monto_total), 0) as ventas, COALESCE(SUM(tasa_servicio), 0) as tasas')
            ->first();
    }

    public static function latestForDashboard(array $filters, int $limit = 6): Collection
    {
        return self::searchAdmin('', $filters)
            ->withCount('pasajes')
            ->orderByDesc('fecha_compra')
            ->orderByDesc('id')
            ->limit($limit)
            ->get();
    }

    public static function statusCountsForDashboard(array $filters): SupportCollection
    {
        return self::searchAdmin('', $filters)
            ->selectRaw('estado_pago, COUNT(*) as cantidad')
            ->groupBy('estado_pago')
            ->pluck('cantidad', 'estado_pago');
    }

    public static function reservasQueBloqueanAsientos(): Builder
    {
        return self::query()->where(function ($query) {
            $query->whereIn('estado_pago', [
                self::ESTADO_PAGO_PAGADO,
                self::ESTADO_PAGO_PENDIENTE,
            ])->orWhere(function ($query) {
                $query->where('estado_pago', self::ESTADO_PAGO_NUEVO)
                    ->where('fecha_expiracion', '>', now());
            });
        });
    }

    public function pago(): HasOne
    {
        return $this->hasOne(PagoReserva::class, 'reserva_id');
    }
}
