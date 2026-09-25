<?php

namespace App\Models;

use App\Services\Admin\Access;
use App\Traits\AuthenticatesModel;
use App\Traits\TraitGeneral;
use Illuminate\Contracts\Auth\Access\Authorizable;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;

class Admin extends ModelHelper implements Authenticatable, Authorizable, CanResetPassword
{
    use AuthenticatesModel;
    use HasFactory, Notifiable, TraitGeneral;

    const ROOT = 1;

    const SUPERADMIN = 2;

    const ADMIN = 3;

    const ACTIVO = 1;

    const INACTIVO = 2;

    const ELIMINADO = 0;

    protected $table = 'admins';

    protected $guarded = [];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return ['password' => 'hashed', 'level' => 'integer', 'status' => 'integer'];
    }

    public function isRoot(): bool
    {
        return $this->level === self::ROOT;
    }

    public function isSuperAdmin(): bool
    {
        return $this->level === self::SUPERADMIN;
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
        if ($this->status !== self::ACTIVO) {
            return array_fill_keys(array_keys($checks), false);
        }

        if ($this->isRoot()) {
            return array_fill_keys(array_keys($checks), true);
        }
        $permissions = $this->isSuperAdmin() ? collect() : $this->getPermissionsMap();
        $results = [];

        foreach ($checks as $key => [$section, $action]) {
            $results[$key] = $section !== 'admins' && ($this->isSuperAdmin() || $section === 'account' || $permissions->contains(fn ($item) => $item->section_url === $section && $item->permission_url === $action));
        }

        return $results;
    }

    public static function searchUserName(string $username)
    {
        return self::where('username', $username)->where('status', Admin::ACTIVO)->first();
    }

    public static function searchAdmin(string $search = '', array $filters = []): Builder
    {
        $query = self::query();
        $query->where('level', '!=', self::ROOT);

        if ($search !== '') {
            $query->where(
                function ($query) use ($search) {
                    return $query->where('name', 'like', '%'.$search.'%')
                        ->orWhere('username', 'like', '%'.$search.'%')
                        ->orWhere('email', 'like', '%'.$search.'%');
                },
            );
        }

        $status = $filters['status'] ?? null;

        if ($status !== null && $status !== '') {
            $query->where('status', $status);
        } else {
            $query->where('status', '!=', self::ELIMINADO);
        }

        return $query;
    }

    public static function searchEmail(string $email)
    {
        return self::where('email', $email)->first();
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    public function reembolsos(): HasMany
    {
        return $this->hasMany(Reembolso::class, 'admin_id');
    }

    public function auditorias(): HasMany
    {
        return $this->hasMany(Auditoria::class, 'admin_id');
    }

    public function ordenesCobroAprobadas(): HasMany
    {
        return $this->hasMany(OrdenCobro::class, 'admin_id');
    }
}
