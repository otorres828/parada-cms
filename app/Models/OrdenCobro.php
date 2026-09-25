<?php

namespace App\Models;

use App\Traits\TraitGeneral;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrdenCobro extends ModelHelper
{
    use TraitGeneral;

    public const ESTATUS_EMITIDO = 1;

    public const ESTATUS_PENDIENTE = 2;

    public const ESTATUS_RECHAZADO = 3;

    public const ESTATUS_APROBADO = 4;

    protected $table = 'ordenes_cobro';

    protected $fillable = [
        'empresa_id',
        'admin_id',
        'codigo',
        'periodo_desde',
        'periodo_hasta',
        'fecha_emision',
        'fecha_vencimiento',
        'cantidad_reservas',
        'total',
        'estatus',
        'referencia_pago',
        'comprobante',
        'fecha_pago_reportado',
        'fecha_aprobacion',
        'notificacion_emitida_at',
        'recordatorio_48_at',
        'recordatorio_24_at',
        'comentarios',
        'reservas_incluidas',
    ];

    protected function casts(): array
    {
        return [
            'admin_id' => 'integer',
            'empresa_id' => 'integer',
            'periodo_desde' => 'datetime',
            'periodo_hasta' => 'datetime',
            'fecha_emision' => 'datetime',
            'fecha_vencimiento' => 'datetime',
            'cantidad_reservas' => 'integer',
            'total' => 'decimal:2',
            'estatus' => 'integer',
            'fecha_pago_reportado' => 'datetime',
            'fecha_aprobacion' => 'datetime',
            'notificacion_emitida_at' => 'datetime',
            'recordatorio_48_at' => 'datetime',
            'recordatorio_24_at' => 'datetime',
            'reservas_incluidas' => 'array',
        ];
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }

    public function getEstatusNombre(): string
    {
        return match ($this->estatus) {
            self::ESTATUS_EMITIDO => 'Emitido',
            self::ESTATUS_PENDIENTE => 'Pendiente',
            self::ESTATUS_RECHAZADO => 'Rechazado',
            self::ESTATUS_APROBADO => 'Aprobado',
            default => 'Desconocido',
        };
    }

    public function estaAbierta(): bool
    {
        return $this->estatus !== self::ESTATUS_APROBADO;
    }

    public static function searchAdmin(string $search = '', array $filters = []): Builder
    {
        $query = self::query()->with(['empresa', 'admin']);

        if ($search !== '') {
            $query->where(function ($query) use ($search) {
                $query->where('ordenes_cobro.id', ctype_digit($search) ? $search : -1);
                $query->orWhere('ordenes_cobro.codigo', 'like', '%'.$search.'%');
                $query->orWhere('ordenes_cobro.referencia_pago', 'like', '%'.$search.'%');
                $query->orWhereHas('empresa', function ($query) use ($search) {
                    $query->where('nombre', 'like', '%'.$search.'%');
                });
            });
        }

        if (! empty($filters['empresa_id'])) {
            $query->where('ordenes_cobro.empresa_id', $filters['empresa_id']);
        }

        if (($filters['estatus'] ?? '') !== '') {
            $query->where('ordenes_cobro.estatus', $filters['estatus']);
        }

        if (! empty($filters['date_from'])) {
            $query->whereDate('ordenes_cobro.fecha_emision', '>=', self::date($filters['date_from']));
        }

        if (! empty($filters['date_to'])) {
            $query->whereDate('ordenes_cobro.fecha_emision', '<=', self::date($filters['date_to']));
        }

        return $query;
    }

    public static function findAdminDetail(int $ordenCobroId): self
    {
        return self::query()
            ->with(['empresa', 'admin'])
            ->findOrFail($ordenCobroId);
    }
}
