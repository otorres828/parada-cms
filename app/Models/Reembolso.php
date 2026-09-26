<?php

namespace App\Models;

use App\Traits\TraitGeneral;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reembolso extends ModelHelper
{
    use TraitGeneral;

    protected $table = 'reembolsos';

    protected $fillable = [
        'pago_reserva_id',
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

    public function pagoReserva(): BelongsTo
    {
        return $this->belongsTo(PagoReserva::class, 'pago_reserva_id');
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

    public function calcularMontoBs(string|int|float|null $monto = null): ?string
    {
        return $this->pagoReserva?->calcularMontoBs($monto ?? $this->monto);
    }

    public static function searchAdmin(string $search = '', array $filters = []): Builder
    {
        $query = self::query()->with([0 => 'empresa', 1 => 'pagoReserva.reserva.tipoCambio', 2 => 'admin', 3 => 'revisor']);

        if ($search !== '') {
            $query->where(function ($query) use ($search) {
                $query->where('reembolsos.id', ctype_digit($search) ? $search : -1);
                $query->orWhere('reembolsos.referencia', 'like', '%' . $search . '%');
                $query->orWhere('reembolsos.motivo', 'like', '%' . $search . '%');
                $query->orWhereHas('empresa', function ($query) use ($search) {
                    return $query->where('nombre', 'like', '%' . $search . '%');
                });
            });
        }

        $status = $filters['status'] ?? $filters['estatus'] ?? $filters['estado_pago'] ?? null;

        if ($status !== null && $status !== '') {
            $query->where('reembolsos.estatus', $status);
        }

        if (isset($filters['empresa_id']) && $filters['empresa_id'] !== '') {
            $query->where('reembolsos.empresa_id', $filters['empresa_id']);
        }

        if (isset($filters['pago_reserva_id'])) {
            $query->where('reembolsos.pago_reserva_id', $filters['pago_reserva_id']);
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate('reembolsos.created_at', '>=', self::date($filters['date_from']));
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('reembolsos.created_at', '<=', self::date($filters['date_to']));
        }

        return $query;
    }

}
