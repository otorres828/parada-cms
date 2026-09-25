<?php

namespace App\Models;

use App\Traits\TraitGeneral;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Validation\ValidationException;

class ExoneracionTasaServicio extends ModelHelper
{
    use TraitGeneral;

    public const ACTIVO = 1;

    public const INACTIVO = 2;

    public const ELIMINADO = 0;

    protected $table = 'exoneraciones_tasa_servicio';

    protected $fillable = [
        'empresa_id',
        'fecha_desde',
        'fecha_hasta',
        'motivo',
        'estatus',
    ];

    protected function casts(): array
    {
        return [
            'empresa_id' => 'integer',
            'fecha_desde' => 'datetime',
            'fecha_hasta' => 'datetime',
            'estatus' => 'integer',
        ];
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public static function vigenteParaEmpresa(int $empresaId): ?self
    {
        return self::query()
            ->where('empresa_id', $empresaId)
            ->where('estatus', self::ACTIVO)
            ->where('fecha_desde', '<=', now())
            ->where(function ($query) {
                $query->whereNull('fecha_hasta')
                    ->orWhere('fecha_hasta', '>=', now());
            })
            ->orderByDesc('fecha_desde')
            ->first();
    }

    public static function searchAdmin(string $search = '', array $filters = []): Builder
    {
        $query = self::query()->with('empresa');

        if ($search !== '') {
            $query->where(function ($query) use ($search) {
                $query->where('exoneraciones_tasa_servicio.id', ctype_digit($search) ? $search : -1)
                    ->orWhere('exoneraciones_tasa_servicio.motivo', 'like', '%'.$search.'%')
                    ->orWhereHas('empresa', function ($query) use ($search) {
                        $query->where('nombre', 'like', '%'.$search.'%');
                    });
            });
        }

        if (! empty($filters['empresa_id'])) {
            $query->where('empresa_id', $filters['empresa_id']);
        }

        if (($filters['status'] ?? '') !== '') {
            $query->where('estatus', $filters['status']);
        } else {
            $query->where('estatus', '!=', self::ELIMINADO);
        }

        return $query;
    }

    public function validarSolapamiento(): void
    {
        if ($this->estatus !== self::ACTIVO) {
            return;
        }

        $query = self::query()
            ->where('empresa_id', $this->empresa_id)
            ->where('estatus', self::ACTIVO)
            ->when($this->exists, function ($query) {
                $query->whereKeyNot($this->id);
            })
            ->where(function ($query) {
                $query->whereNull('fecha_hasta')
                    ->orWhere('fecha_hasta', '>=', $this->fecha_desde);
            });

        if ($this->fecha_hasta !== null) {
            $query->where('fecha_desde', '<=', $this->fecha_hasta);
        }

        if ($query->exists()) {
            throw ValidationException::withMessages([
                'fecha_desde' => 'La empresa ya tiene una exoneración activa que coincide con este período.',
            ]);
        }
    }

    public function snapshot(): array
    {
        return [
            'exoneracion_id' => $this->id,
            'fecha_desde' => $this->fecha_desde?->format('Y-m-d H:i:s'),
            'fecha_hasta' => $this->fecha_hasta?->format('Y-m-d H:i:s'),
            'motivo' => $this->motivo,
        ];
    }
}
