<?php

namespace App\Models;

use App\Traits\TraitGeneral;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ConfiguracionCupon extends ModelHelper
{
    use TraitGeneral;

    const TIPO_RANDOM = 1;

    const TIPO_PERSONALIZADO = 2;

    const MODALIDAD_GENERAL = 'GENERAL';

    const MODALIDAD_PRIMERA_COMPRA = 'PRIMERA_COMPRA';

    const MODALIDAD_USUARIO_NUEVO = 'USUARIO_NUEVO';

    const APLICA_EN_RESERVA = 'reserva';

    const APLICA_EN_PASAJES = 'pasajes';

    protected $table = 'configuracion_cupones';

    protected $fillable = [
        'empresa_id',
        'nombre_campana',
        'tipo_cupon',
        'modalidad',
        'aplica_en',
        'codigo_personalizado',
        'cantidad_generar',
        'tipo_descuento',
        'monto_descuento',
        'fecha_inicio',
        'fecha_fin',
        'estatus',
    ];

    protected function casts(): array
    {
        return ['tipo_cupon' => 'integer', 'cantidad_generar' => 'integer', 'monto_descuento' => 'decimal:2', 'fecha_inicio' => 'datetime', 'fecha_fin' => 'datetime', 'estatus' => 'integer'];
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function cupones(): HasMany
    {
        return $this->hasMany(Cupon::class, 'configuracion_cupon_id');
    }

    public static function searchAdmin(string $search = '', array $filters = []): Builder
    {
        $query = self::query()->with('empresa')->withCount('cupones');

        if ($search !== '') {
            $query->where(function ($query) use ($search) {
                $query->where('configuracion_cupones.id', ctype_digit($search) ? $search : -1);
                $query->orWhere('configuracion_cupones.nombre_campana', 'like', '%' . $search . '%');
                $query->orWhere('configuracion_cupones.codigo_personalizado', 'like', '%' . $search . '%');
            });
        }

        $status = $filters['status'] ?? $filters['estatus'] ?? $filters['estado_pago'] ?? null;

        if ($status !== null && $status !== '') {
            $query->where('configuracion_cupones.estatus', $status);
        }

        if (isset($filters['empresa_id'])) {
            $query->where('configuracion_cupones.empresa_id', $filters['empresa_id']);
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate('configuracion_cupones.fecha_inicio', '>=', self::date($filters['date_from']));
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('configuracion_cupones.fecha_inicio', '<=', self::date($filters['date_to']));
        }

        return $query;
    }
}
