<?php

namespace App\Models;

use App\Traits\TraitGeneral;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Amenidad extends ModelHelper
{
    use TraitGeneral;

    protected $table = 'amenidades';

    protected $fillable = [
        'nombre',
        'icono',
        'estatus',
    ];

    protected function casts(): array
    {
        return ['estatus' => 'boolean'];
    }

    public function amenidadAutobus(): HasMany
    {
        return $this->hasMany(AmenidadAutobus::class, 'amenidad_id');
    }

    public function autobuses(): BelongsToMany
    {
        return $this->belongsToMany(Autobus::class, 'amenidad_autobus', 'amenidad_id', 'autobus_id')->withTimestamps();
    }

    public static function searchAdmin(string $search = '', array $filters = []): Builder
    {
        $query = self::query();

        if ($search !== '') {
            $query->where(function ($query) use ($search) {
                $query->where('amenidades.id', ctype_digit($search) ? $search : -1);
                $query->orWhere('amenidades.nombre', 'like', '%' . $search . '%');
            });
        }

        $status = $filters['status'] ?? $filters['estatus'] ?? $filters['estado_pago'] ?? null;

        if ($status !== null && $status !== '') {
            $query->where('amenidades.estatus', $status);
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate('amenidades.created_at', '>=', self::date($filters['date_from']));
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('amenidades.created_at', '<=', self::date($filters['date_to']));
        }

        return $query;
    }
}
