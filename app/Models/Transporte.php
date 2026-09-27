<?php

namespace App\Models;

use App\Traits\TraitGeneral;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Transporte extends ModelHelper
{
    use TraitGeneral;

    public const AUTOBUS = 'autobus';
    public const CARRO = 'carro';

    protected $attributes = ['tipo_transporte' => self::AUTOBUS];

    protected $table = 'transportes';

    protected $fillable = [
        'empresa_id',
        'tipo_transporte',
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

    public function getTipoTransporte(): string
    {
        return match ($this->tipo_transporte) {
            self::AUTOBUS => 'Autobús',
            self::CARRO => 'Carro',
            default => 'No registrado',
        };
    }

    public function amenidadTransporte(): HasMany
    {
        return $this->hasMany(AmenidadTransporte::class, 'transporte_id');
    }

    public function programaciones(): HasMany
    {
        return $this->hasMany(Programacion::class, 'transporte_id');
    }

    public function amenidades(): BelongsToMany
    {
        return $this->belongsToMany(Amenidad::class, 'amenidad_transporte', 'transporte_id', 'amenidad_id')->withTimestamps();
    }

    public static function searchAdmin(string $search = '', array $filters = []): Builder
    {
        $query = self::query()->with([0 => 'empresa', 1 => 'amenidades']);

        if (! empty($filters['tipo_transporte'])) {
            $query->where('tipo_transporte', $filters['tipo_transporte']);
        }

        if ($search !== '') {
            $query->where(function ($query) use ($search) {
                $query->where('transportes.id', ctype_digit($search) ? $search : -1);
                $query->orWhere('transportes.placa', 'like', '%'.$search.'%');
                $query->orWhere('transportes.modelo', 'like', '%'.$search.'%');
                $query->orWhereHas('empresa', function ($query) use ($search) {
                    return $query->where('nombre', 'like', '%'.$search.'%');
                });
            });
        }

        $status = $filters['status'] ?? $filters['estatus'] ?? $filters['estado_pago'] ?? null;

        if ($status !== null && $status !== '') {
            $query->where('transportes.estatus', $status);
        } else {
            $query->where('transportes.estatus', '!=', self::ESTADO_DELETE);
        }

        if (isset($filters['empresa_id']) && $filters['empresa_id'] !== '') {
            $query->where('transportes.empresa_id', $filters['empresa_id']);
        }

        if (! empty($filters['date_from'])) {
            $query->whereDate('transportes.created_at', '>=', self::date($filters['date_from']));
        }

        if (! empty($filters['date_to'])) {
            $query->whereDate('transportes.created_at', '<=', self::date($filters['date_to']));
        }

        return $query;
    }
}
