<?php

namespace App\Models;

use App\Traits\TraitGeneral;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Movimiento extends ModelHelper
{
    use TraitGeneral;

    protected $table = 'movimientos';

    protected $fillable = [
        'empresa_id',
        'admin_id',
        'pago_id',
        'retiro_id',
        'reembolso_id',
        'clave',
        'tipo',
        'monto',
        'moneda',
        'descripcion',
    ];

    protected function casts(): array
    {
        return ['monto' => 'decimal:2'];
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }

    public function pago(): BelongsTo
    {
        return $this->belongsTo(Pago::class, 'pago_id');
    }

    public function retiro(): BelongsTo
    {
        return $this->belongsTo(Retiro::class, 'retiro_id');
    }

    public function reembolso(): BelongsTo
    {
        return $this->belongsTo(Reembolso::class, 'reembolso_id');
    }

    public static function searchAdmin(string $search = '', array $filters = []): Builder
    {
        $query = self::query()->with([0 => 'empresa', 1 => 'admin']);

        if ($search !== '') {
            $query->where(function ($query) use ($search) {
                $query->where('movimientos.id', ctype_digit($search) ? $search : -1);
                $query->orWhere('movimientos.clave', 'like', '%' . $search . '%');
                $query->orWhere('movimientos.descripcion', 'like', '%' . $search . '%');
                $query->orWhereHas('empresa', function ($query) use ($search) {
                    return $query->where('nombre', 'like', '%' . $search . '%');
                });
            });
        }

        if (isset($filters['empresa_id']) && $filters['empresa_id'] !== '') {
            $query->where('movimientos.empresa_id', $filters['empresa_id']);
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate('movimientos.created_at', '>=', self::date($filters['date_from']));
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('movimientos.created_at', '<=', self::date($filters['date_to']));
        }

        return $query;
    }
}
