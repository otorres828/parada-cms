<?php

namespace App\Models;

use App\Traits\TraitGeneral;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Retiro extends ModelHelper
{
    use TraitGeneral;

    protected $table = 'retiros';

    protected $fillable = [
        'empresa_id',
        'admin_id',
        'revisado_por',
        'monto',
        'moneda',
        'estatus',
        'datos_bancarios',
        'comentario',
        'referencia',
        'comprobante',
        'fecha_resolucion',
    ];

    protected function casts(): array
    {
        return ['monto' => 'decimal:2', 'fecha_resolucion' => 'datetime'];
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }

    public function revisor(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'revisado_por');
    }

    public static function searchAdmin(string $search = '', array $filters = []): Builder
    {
        $query = self::query()->with([0 => 'empresa', 1 => 'admin', 2 => 'revisor']);

        if ($search !== '') {
            $query->where(function ($query) use ($search) {
                $query->where('retiros.id', ctype_digit($search) ? $search : -1);
                $query->orWhere('retiros.referencia', 'like', '%' . $search . '%');
                $query->orWhereHas('empresa', function ($query) use ($search) {
                    return $query->where('nombre', 'like', '%' . $search . '%');
                });
            });
        }

        $status = $filters['status'] ?? $filters['estatus'] ?? $filters['estado_pago'] ?? null;

        if ($status !== null && $status !== '') {
            $query->where('retiros.estatus', $status);
        }

        if (isset($filters['empresa_id']) && $filters['empresa_id'] !== '') {
            $query->where('retiros.empresa_id', $filters['empresa_id']);
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate('retiros.created_at', '>=', self::date($filters['date_from']));
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('retiros.created_at', '<=', self::date($filters['date_to']));
        }

        return $query;
    }

    public function movimientos(): HasMany
    {
        return $this->hasMany(Movimiento::class, 'retiro_id');
    }
}
