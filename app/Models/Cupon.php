<?php

namespace App\Models;

use App\Traits\TraitGeneral;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cupon extends ModelHelper
{
    use TraitGeneral;

    protected $table = 'cupones';

    protected $fillable = [
        'configuracion_cupon_id',
        'usuario_id',
        'codigo',
        'redimido',
        'fecha_redencion',
    ];

    protected function casts(): array
    {
        return ['redimido' => 'boolean', 'fecha_redencion' => 'datetime'];
    }

    public function configuracionCupon(): BelongsTo
    {
        return $this->belongsTo(ConfiguracionCupon::class, 'configuracion_cupon_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function reservas(): HasMany
    {
        return $this->hasMany(Reserva::class, 'cupon_id');
    }

    public static function searchAdmin(string $search = '', array $filters = []): Builder
    {
        $query = self::query()->with([0 => 'configuracionCupon', 1 => 'usuario']);

        if ($search !== '') {
            $query->where(function ($query) use ($search) {
                $query->where('cupones.id', ctype_digit($search) ? $search : -1);
                $query->orWhere('cupones.codigo', 'like', '%' . $search . '%');
            });
        }

        $status = $filters['status'] ?? $filters['estatus'] ?? $filters['estado_pago'] ?? null;

        if ($status !== null && $status !== '') {
            $query->where('cupones.redimido', $status);
        }

        if (isset($filters['configuracion_cupon_id'])) {
            $query->where('cupones.configuracion_cupon_id', $filters['configuracion_cupon_id']);
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate('cupones.created_at', '>=', self::date($filters['date_from']));
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('cupones.created_at', '<=', self::date($filters['date_to']));
        }

        return $query;
    }
}
