<?php

namespace App\Models;

use App\Traits\AuthenticatesModel;
use App\Traits\TraitGeneral;
use Illuminate\Contracts\Auth\Access\Authorizable;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UsuarioEmpresa extends ModelHelper implements Authenticatable, Authorizable, CanResetPassword
{
    use AuthenticatesModel;
    use TraitGeneral;

    protected $table = 'usuarios_empresa';

    protected $fillable = [
        'empresa_id',
        'nombre',
        'email',
        'password',
        'es_admin',
        'estatus',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return ['es_admin' => 'boolean', 'estatus' => 'integer', 'password' => 'hashed'];
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function permisosUsuariosEmpresa(): HasMany
    {
        return $this->hasMany(PermisoUsuarioEmpresa::class, 'usuario_empresa_id');
    }

    public function permisos(): BelongsToMany
    {
        return $this->belongsToMany(PermissionEmpresa::class, 'permisos_usuarios_empresa', 'usuario_empresa_id', 'permiso_id')->withTimestamps();
    }

    public static function searchAdmin(string $search = '', array $filters = []): Builder
    {
        $query = self::query();

        if ($search !== '') {
            $query->where(function ($query) use ($search) {
                $query->where('usuarios_empresa.id', ctype_digit($search) ? $search : -1);
                $query->orWhere('usuarios_empresa.nombre', 'like', '%'.$search.'%');
                $query->orWhere('usuarios_empresa.email', 'like', '%'.$search.'%');
            });
        }

        $status = $filters['status'] ?? $filters['estatus'] ?? $filters['estado_pago'] ?? null;

        if ($status !== null && $status !== '') {
            $query->where('usuarios_empresa.estatus', $status);
        } else {
            $query->where('usuarios_empresa.estatus', '!=', self::ESTADO_DELETE);
        }

        if (isset($filters['empresa_id'])) {
            $query->where('usuarios_empresa.empresa_id', $filters['empresa_id']);
        }

        if (! empty($filters['date_from'])) {
            $query->whereDate('usuarios_empresa.created_at', '>=', self::date($filters['date_from']));
        }

        if (! empty($filters['date_to'])) {
            $query->whereDate('usuarios_empresa.created_at', '<=', self::date($filters['date_to']));
        }

        return $query;
    }

    public static function findAdminByCompany(int $userId, int $companyId, bool $lockForUpdate = false): self
    {
        $query = self::query()->where('empresa_id', $companyId);

        if ($lockForUpdate) {
            $query->lockForUpdate();
        }

        return $query->findOrFail($userId);
    }
}
