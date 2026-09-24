<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;

class SolicitudEmpresa extends ModelHelper
{
    public const ESTADO_NUEVA = 1;

    public const ESTADO_CONTACTADA = 2;

    public const ESTADO_CERRADA = 3;

    protected $table = 'solicitudes_empresas';

    protected $fillable = [
        'nombre',
        'empresa',
        'cargo',
        'telefono',
        'email',
        'ciudad',
        'mensaje',
        'estatus',
    ];

    protected function casts(): array
    {
        return [
            'estatus' => 'integer',
        ];
    }

    public static function searchAdmin(string $search = '', array $filters = []): Builder
    {
        $query = self::query();

        if ($search !== '') {
            $query->where(function ($query) use ($search) {
                $query->where('nombre', 'like', '%' . $search . '%')
                    ->orWhere('empresa', 'like', '%' . $search . '%')
                    ->orWhere('telefono', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%')
                    ->orWhere('ciudad', 'like', '%' . $search . '%');
            });
        }

        if (isset($filters['estatus']) && $filters['estatus'] !== '') {
            $query->where('estatus', (int) $filters['estatus']);
        }

        return $query;
    }

    public function getEstatusNombreAttribute(): string
    {
        return match ($this->estatus) {
            self::ESTADO_NUEVA => 'Nueva',
            self::ESTADO_CONTACTADA => 'Contactada',
            self::ESTADO_CERRADA => 'Cerrada',
            default => 'Sin estado',
        };
    }
}
