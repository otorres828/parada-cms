<?php

namespace App\Models;

use App\Traits\TraitGeneral;
use Illuminate\Database\Eloquent\Builder;

class Configuracion extends ModelHelper
{
    use TraitGeneral;

    protected $table = 'configuraciones';

    protected $fillable = [
        'grupo',
        'valores',
    ];

    protected function casts(): array
    {
        return ['valores' => 'array'];
    }

    public static function searchAdmin(string $search = '', array $filters = []): Builder
    {
        $query = self::query();

        if ($search !== '') {
            $query->where(function ($query) use ($search) {
                $query->where('configuraciones.id', ctype_digit($search) ? $search : -1);
            });
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate('configuraciones.created_at', '>=', self::date($filters['date_from']));
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('configuraciones.created_at', '<=', self::date($filters['date_to']));
        }

        return $query;
    }
}
