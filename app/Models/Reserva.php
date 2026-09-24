<?php

namespace App\Models;

use App\Traits\TraitGeneral;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

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
        'codigo_referencia',
        'monto_pasajes',
        'descuento_aplicado',
        'tasa_servicio',
        'monto_total',
        'estado_pago',
        'metodo_pago',
        'fecha_compra',
        'fecha_expiracion',
        'comentarios_auditoria',
    ];

    const ESTADO_PAGO_NUEVO = 1;

    const ESTADO_PAGO_PAGADO = 2;

    const ESTADO_PAGO_PENDIENTE = 3;

    const ESTADO_PAGO_CANCELADO = 4;

    const ESTADO_PAGO_REEMBOLSADO = 5;

    const ESTADO_PAGO_FALLIDO = 6;

    const METODO_TRANSFERENCIA = 1;

    protected function casts(): array
    {
        return ['estado_pago' => 'integer', 'metodo_pago' => 'integer', 'monto_pasajes' => 'decimal:2', 'descuento_aplicado' => 'decimal:2', 'tasa_servicio' => 'decimal:2', 'monto_total' => 'decimal:2', 'fecha_compra' => 'datetime', 'fecha_expiracion' => 'datetime', 'comentarios_auditoria' => 'array'];
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

    public function pasajes(): HasMany
    {
        return $this->hasMany(Pasaje::class, 'reserva_id');
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
            $query->where('reservas.estado_pago', $status);
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

    public function pago(): HasOne
    {
        return $this->hasOne(PagoReserva::class, 'reserva_id');
    }
}
