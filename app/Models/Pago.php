<?php

namespace App\Models;

use App\Traits\TraitGeneral;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pago extends ModelHelper
{
    use TraitGeneral;

    protected $table = 'pagos';

    protected $fillable = [
        'reserva_id',
        'empresa_id',
        'admin_id',
        'monto',
        'neto_empresa',
        'comision',
        'moneda',
        'referencia',
        'metodo',
        'comprobante',
        'comentario',
        'fecha_pago',
    ];

    protected function casts(): array
    {
        return ['monto' => 'decimal:2', 'neto_empresa' => 'decimal:2', 'comision' => 'decimal:2', 'fecha_pago' => 'datetime'];
    }

    public function reserva(): BelongsTo
    {
        return $this->belongsTo(Reserva::class, 'reserva_id');
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }

    public static function searchAdmin(string $search = '', array $filters = []): Builder
    {
        $query = self::query()->with([0 => 'empresa', 1 => 'reserva', 2 => 'admin']);

        if ($search !== '') {
            $query->where(function ($query) use ($search) {
                $query->where('pagos.id', ctype_digit($search) ? $search : -1);
                $query->orWhere('pagos.referencia', 'like', '%' . $search . '%');
                $query->orWhere('pagos.metodo', 'like', '%' . $search . '%');
                $query->orWhereHas('empresa', function ($query) use ($search) {
                    return $query->where('nombre', 'like', '%' . $search . '%');
                });
            });
        }

        if (isset($filters['empresa_id']) && $filters['empresa_id'] !== '') {
            $query->where('pagos.empresa_id', $filters['empresa_id']);
        }

        if (isset($filters['reserva_id'])) {
            $query->where('pagos.reserva_id', $filters['reserva_id']);
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate('pagos.fecha_pago', '>=', self::date($filters['date_from']));
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('pagos.fecha_pago', '<=', self::date($filters['date_to']));
        }

        if (!empty($filters['con_pasajeros'])) {
            $query->with('reserva.pasajes.viajero');
        }

        return $query;
    }

    public function reembolsos(): HasMany
    {
        return $this->hasMany(Reembolso::class, 'pago_id');
    }

    public function movimientos(): HasMany
    {
        return $this->hasMany(Movimiento::class, 'pago_id');
    }
}
