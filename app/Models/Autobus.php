<?php

namespace App\Models;

use App\Traits\TraitGeneral;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Autobus extends ModelHelper
{
    use TraitGeneral;

    protected $table = 'autobuses';

    protected $fillable = [
        'empresa_id',
        'placa',
        'modelo',
        'tipo_asiento',
        'total_asientos',
        'es_plantilla',
        'estatus',
    ];

    protected function casts(): array
    {
        return ['total_asientos' => 'integer', 'es_plantilla' => 'boolean', 'estatus' => 'integer'];
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function amenidadAutobus(): HasMany
    {
        return $this->hasMany(AmenidadAutobus::class, 'autobus_id');
    }

    public function programaciones(): HasMany
    {
        return $this->hasMany(Programacion::class, 'autobus_id');
    }

    public function amenidades(): BelongsToMany
    {
        return $this->belongsToMany(Amenidad::class, 'amenidad_autobus', 'autobus_id', 'amenidad_id')->withTimestamps();
    }

    public static function searchAdmin(string $search = '', array $filters = []): Builder
    {
        $query = self::query()->with([0 => 'empresa', 1 => 'amenidades']);

        if ($search !== '') {
            $query->where(function ($query) use ($search) {
                $query->where('autobuses.id', ctype_digit($search) ? $search : -1);
                $query->orWhere('autobuses.placa', 'like', '%'.$search.'%');
                $query->orWhere('autobuses.modelo', 'like', '%'.$search.'%');
                $query->orWhereHas('empresa', function ($query) use ($search) {
                    return $query->where('nombre', 'like', '%'.$search.'%');
                });
            });
        }

        $status = $filters['status'] ?? $filters['estatus'] ?? $filters['estado_pago'] ?? null;

        if ($status !== null && $status !== '') {
            $query->where('autobuses.estatus', $status);
        } else {
            $query->where('autobuses.estatus', '!=', self::ESTADO_DELETE);
        }

        if (isset($filters['empresa_id']) && $filters['empresa_id'] !== '') {
            $query->where('autobuses.empresa_id', $filters['empresa_id']);
        }

        if (! empty($filters['date_from'])) {
            $query->whereDate('autobuses.created_at', '>=', self::date($filters['date_from']));
        }

        if (! empty($filters['date_to'])) {
            $query->whereDate('autobuses.created_at', '<=', self::date($filters['date_to']));
        }

        return $query;
    }
}
