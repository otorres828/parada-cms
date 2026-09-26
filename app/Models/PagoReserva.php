<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PagoReserva extends ModelHelper
{
    protected $table = 'pagos_reservas';

    protected $fillable = ['reserva_id', 'total', 'tasa_servicio', 'metodo_pago', 'referencia_pago', 'fecha_pago', 'comprobante'];

    protected function casts(): array
    {
        return ['total' => 'decimal:2', 'tasa_servicio' => 'decimal:2', 'metodo_pago' => 'integer', 'fecha_pago' => 'datetime'];
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
}
