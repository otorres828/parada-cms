<?php

namespace App\Models;

use App\Services\Empresa\Access;
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

    const SUPERADMIN = 1;

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
            $query->whereDate('usuarios_empresa.created_at', '<=', self::date($filters['date_to'], 'date_to'));
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

    public static function searchUserName(string $username)
    {
        return self::where('email', $username)->where('estatus', self::ESTADO_ACTIVE)->first();
    }


    public function isSuperAdmin(): bool
    {
        return $this->es_admin === self::SUPERADMIN;
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(PermissionAdmin::class, 'permission_admin_admin', 'admin_id', 'permission_id')->withPivot('status');
    }

    public function getPermissionsMap()
    {
        return Access::permissions($this);
    }

    public function hasPermission(string $sectionURL, string $permissionURL): bool
    {
        return $this->checkPermissionsBatch(['check' => [$sectionURL, $permissionURL]])['check'];
    }

    public function checkPermissionsBatch(array $checks): array
    {
        if ($this->estatus !== self::ESTADO_ACTIVE) {
            return array_fill_keys(array_keys($checks), false);
        }

        if ($this->isSuperAdmin()) {
            return array_fill_keys(array_keys($checks), true);
        }
        $permissions = $this->isSuperAdmin() ? collect() : $this->getPermissionsMap();
        $results = [];

        foreach ($checks as $key => [$section, $action]) {
            $results[$key] = $section !== 'admins' && ($this->isSuperAdmin() || $section === 'account' || $permissions->contains(fn ($item) => $item->section_url === $section && $item->permission_url === $action));
        }

        return $results;
    }

}
