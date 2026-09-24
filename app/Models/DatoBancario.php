<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DatoBancario extends ModelHelper
{
    public const PAGO_MOVIL = 1;

    public const CUENTA_BANCARIA = 2;

    public const ACTIVO = 1;

    public const INACTIVO = 2;

    public const ELIMINADO = 0;

    protected $table = 'datos_bancarios';

    protected $fillable = [
        'empresa_id',
        'tipo',
        'banco',
        'nombre_titular',
        'tipo_titular',
        'numero_documento',
        'numero_cuenta_telefono',
        'tipo_cuenta',
        'estatus',
    ];

    protected function casts(): array
    {
        return [
            'tipo' => 'integer',
            'estatus' => 'integer',
        ];
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public static function searchAdmin(string $search = '', array $filters = []): Builder
    {
        $query = self::query()->with('empresa');

        if ($search !== '') {
            $query->where(function ($query) use ($search) {
                $query->where('datos_bancarios.nombre_titular', 'like', '%'.$search.'%');
                $query->orWhere('datos_bancarios.numero_documento', 'like', '%'.$search.'%');
                $query->orWhere('datos_bancarios.numero_cuenta_telefono', 'like', '%'.$search.'%');
                $query->orWhere('datos_bancarios.banco', 'like', '%'.$search.'%');
            });
        }

        $status = $filters['status'] ?? $filters['estatus'] ?? null;

        if ($status !== null && $status !== '') {
            $query->where('datos_bancarios.estatus', $status);
        } else {
            $query->where('datos_bancarios.estatus', '!=', self::ELIMINADO);
        }

        if (isset($filters['empresa_id']) && $filters['empresa_id'] !== '') {
            $query->where('datos_bancarios.empresa_id', $filters['empresa_id']);
        }

        return $query;
    }

    public function getTipo(): string
    {
        return match ($this->tipo) {
            self::PAGO_MOVIL => 'Pago móvil',
            self::CUENTA_BANCARIA => 'Cuenta bancaria',
            default => 'Desconocido',
        };
    }

    public function getTipoTitular(): string
    {
        return match ($this->tipo_titular) {
            'juridico' => 'Jurídico',
            'extranjero' => 'Extranjero',
            'personal' => 'Personal',
            default => 'Desconocido',
        };
    }

    public function getTipoCuenta(): string
    {
        return match ($this->tipo_cuenta) {
            'corriente' => 'Corriente',
            'ahorro' => 'Ahorro',
            default => 'No aplica',
        };
    }
}
