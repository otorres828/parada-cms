<?php

namespace App\Models;

use App\Traits\TraitGeneral;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Auditoria extends ModelHelper
{
    use TraitGeneral;

    protected $table = 'auditorias';

    protected $fillable = [
        'admin_id',
        'accion',
        'entidad',
        'entidad_id',
        'datos',
        'ip',
    ];

    protected function casts(): array
    {
        return ['datos' => 'array'];
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }

    public static function searchAdmin(string $search = '', array $filters = []): Builder
    {
        $query = self::query()->with([0 => 'admin']);

        if ($search !== '') {
            $query->where(function ($query) use ($search) {
                $query->where('auditorias.id', ctype_digit($search) ? $search : -1);
                $query->orWhere('auditorias.accion', 'like', '%' . $search . '%');
                $query->orWhere('auditorias.entidad', 'like', '%' . $search . '%');
            });
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate('auditorias.created_at', '>=', self::date($filters['date_from']));
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('auditorias.created_at', '<=', self::date($filters['date_to']));
        }

        return $query;
    }
}
