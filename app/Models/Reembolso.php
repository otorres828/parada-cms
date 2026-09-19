<?php

namespace App\Models;

use App\Traits\TraitGeneral;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Reembolso extends ModelHelper
{
    use TraitGeneral;

    protected $table = 'reembolsos';

    protected $fillable = [
        'pago_id',
        'empresa_id',
        'admin_id',
        'revisado_por',
        'monto',
        'moneda',
        'estatus',
        'motivo',
        'comentario',
        'referencia',
        'comprobante',
        'fecha_resolucion',
    ];

    protected function casts(): array
    {
        return ['monto' => 'decimal:2', 'fecha_resolucion' => 'datetime'];
    }

    public function pago(): BelongsTo
    {
        return $this->belongsTo(Pago::class, 'pago_id');
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
        $query = self::query()->with([0 => 'empresa', 1 => 'pago.reserva', 2 => 'admin', 3 => 'revisor']);

        if ($search !== '') {
            $query->where(function ($query) use ($search) {
                $query->where('reembolsos.id', ctype_digit($search) ? $search : -1);
                $query->orWhere('reembolsos.referencia', 'like', '%' . $search . '%');
                $query->orWhere('reembolsos.motivo', 'like', '%' . $search . '%');
                $query->orWhereHas('empresa', fn ($q) => $q->where('nombre', 'like', '%' . $search . '%'));
            });
        }

        $status = $filters['status'] ?? $filters['estatus'] ?? $filters['estado_pago'] ?? null;

        if ($status !== null && $status !== '') {
            $query->where('reembolsos.estatus', $status);
        }

        if (isset($filters['empresa_id']) && $filters['empresa_id'] !== '') {
            $query->where('reembolsos.empresa_id', $filters['empresa_id']);
        }

        if (isset($filters['pago_id'])) {
            $query->where('reembolsos.pago_id', $filters['pago_id']);
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate('reembolsos.created_at', '>=', self::date($filters['date_from']));
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('reembolsos.created_at', '<=', self::date($filters['date_to']));
        }

        return $query;
    }

    public function movimientos(): HasMany
    {
        return $this->hasMany(Movimiento::class, 'reembolso_id');
    }
}
