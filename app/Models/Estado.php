<?php

namespace App\Models;

use App\Traits\TraitGeneral;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Estado extends ModelHelper
{
    use TraitGeneral;

    protected $table = 'estados';

    protected $fillable = [
        'nombre',
    ];

    protected function casts(): array
    {
        return [];
    }

    public function terminales(): HasMany
    {
        return $this->hasMany(Terminal::class, 'estado_id');
    }

    public static function searchAdmin(string $search = '', array $filters = []): Builder
    {
        $query = self::query();

        if ($search !== '') {
            $query->where(function ($query) use ($search) {
                $query->where('estados.id', ctype_digit($search) ? $search : -1);
                $query->orWhere('estados.nombre', 'like', '%' . $search . '%');
            });
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate('estados.created_at', '>=', self::date($filters['date_from']));
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('estados.created_at', '<=', self::date($filters['date_to']));
        }

        return $query;
    }
}
