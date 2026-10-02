<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PagoReserva extends ModelHelper
{
    public const TIPO_PAGO_TRANSFERENCIA = 1;

    public const TIPO_PAGO_EFECTIVO = 2;

    public const TIPO_PAGO_MOVIL = 3;

    public const TIPO_PAGO_TARJETA = 4;

    public const NAME_TIPO_PAGO = [
        self::TIPO_PAGO_TRANSFERENCIA => 'Transferencia',
        self::TIPO_PAGO_EFECTIVO => 'Efectivo',
        self::TIPO_PAGO_MOVIL => 'Pago Móvil',
        self::TIPO_PAGO_TARJETA => 'Tarjeta',
    ];

    protected $table = 'pagos_reservas';

    protected $fillable = [
        'tipo_pago',
        'moneda',
        'monto_recibido',
        'reserva_id',
        'total',
        'tasa_servicio',
        'metodo_pago',
        'referencia_pago',
        'fecha_pago',
        'comprobante'
    ];

    protected function casts(): array
    {
        return [
            'tipo_pago' => 'integer',
            'monto_recibido' => 'decimal:2',
            'total' => 'decimal:2',
            'tasa_servicio' => 'decimal:2',
            'metodo_pago' => 'integer',
            'fecha_pago' => 'datetime'
        ];
    }


    public function datoBancario(): BelongsTo
    {
        return $this->belongsTo(DatoBancario::class, 'metodo_pago');
    }

    public function reserva(): BelongsTo
    {
        return $this->belongsTo(Reserva::class, 'reserva_id');
    }

    public function calcularMontoBs(string|int|float|null $monto): ?string
    {
        return $this->reserva?->calcularMontoBs($monto);
    }

    public function reembolsos(): HasMany
    {
        return $this->hasMany(Reembolso::class, 'pago_reserva_id');
    }

    public static function searchAdmin(string $search = '', array $filters = []): Builder
    {
        $query = self::query()->with([
            'reserva.programacion.viaje.empresa',
            'reserva.tipoCambio',
            'datoBancario',
        ]);

        if ($search !== '') {
            $query->where(function ($query) use ($search) {
                $query->where('pagos_reservas.id', ctype_digit($search) ? $search : -1)
                    ->orWhere('pagos_reservas.referencia_pago', 'like', '%'.$search.'%')
                    ->orWhereHas('reserva', fn ($reserva) => $reserva->where('codigo_referencia', 'like', '%'.$search.'%'));
            });
        }

        return $query;
    }

    public function getNameTipoPago(): string
    {
        return self::NAME_TIPO_PAGO[$this->tipo_pago] ?? 'Desconocido';
    }
}
