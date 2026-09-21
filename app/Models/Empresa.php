<?php

namespace App\Models;

use App\Traits\TraitGeneral;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Empresa extends ModelHelper
{
    use TraitGeneral;

    protected $table = 'empresas';

    protected $fillable = [
        'nombre',
        'rif',
        'telefono',
        'email',
        'estatus',
        'retiros_habilitados',
        'datos_bancarios',
    ];

    protected function casts(): array
    {
        return ['estatus' => 'integer', 'retiros_habilitados' => 'boolean'];
    }

    public function usuariosEmpresa(): HasMany
    {
        return $this->hasMany(UsuarioEmpresa::class, 'empresa_id');
    }

    public function autobuses(): HasMany
    {
        return $this->hasMany(Autobus::class, 'empresa_id');
    }

    public function viajes(): HasMany
    {
        return $this->hasMany(Viaje::class, 'empresa_id');
    }

    public function configuracionCupones(): HasMany
    {
        return $this->hasMany(ConfiguracionCupon::class, 'empresa_id');
    }

    public static function searchAdmin(string $search = '', array $filters = []): Builder
    {
        $query = self::query();

        if ($search !== '') {
            $query->where(function ($query) use ($search) {
                $query->where('empresas.id', ctype_digit($search) ? $search : -1);
                $query->orWhere('empresas.nombre', 'like', '%' . $search . '%');
                $query->orWhere('empresas.email', 'like', '%' . $search . '%');
                $query->orWhere('empresas.rif', 'like', '%' . $search . '%');
            });
        }

        $status = $filters['status'] ?? $filters['estatus'] ?? $filters['estado_pago'] ?? null;

        if ($status !== null && $status !== '') {
            $query->where('empresas.estatus', $status);
        }else{
            $query->where('empresas.estatus', '!=',self::ESTADO_DELETE);
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate('empresas.created_at', '>=', self::date($filters['date_from']));
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('empresas.created_at', '<=', self::date($filters['date_to']));
        }

        if (!empty($filters['con_legales'])) {
            $query->withCount('documentosLegales');
        }

        return $query;
    }

    public function pagos(): HasMany
    {
        return $this->hasMany(Pago::class, 'empresa_id');
    }

    public function retiros(): HasMany
    {
        return $this->hasMany(Retiro::class, 'empresa_id');
    }

    public function reembolsos(): HasMany
    {
        return $this->hasMany(Reembolso::class, 'empresa_id');
    }

    public function documentosLegales(): HasMany
    {
        return $this->hasMany(DocumentoLegal::class, 'empresa_id');
    }

    public function movimientos(): HasMany
    {
        return $this->hasMany(Movimiento::class, 'empresa_id');
    }
}
