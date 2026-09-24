<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;

class PermissionEmpresa extends ModelHelper
{
    protected $table = 'permissions_empresa';

    public $timestamps = false;

    protected $fillable = [
        0 => 'section_id',
        1 => 'name',
        2 => 'url',
        3 => 'status',
    ];

    protected function casts(): array
    {
        return ['status' => 'boolean'];
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(SectionEmpresa::class, 'section_id');
    }

    public function usuarios(): BelongsToMany
    {
        return $this->belongsToMany(UsuarioEmpresa::class, 'permisos_usuarios_empresa', 'permiso_id', 'usuario_empresa_id')->withTimestamps();
    }

    public static function searchAdmin(string $search = '', array $filters = []): Builder
    {
        $query = self::query()->with([
            0 => 'section.group',
        ]);

        if ($search !== '') {
            $query->where(function ($query) use ($search) {
                return $query->where('name', 'like', '%' . $search . '%')->orWhere('url', 'like', '%' . $search . '%');
            });
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query;
    }

    public static function activeGroupedBySection(): Collection
    {
        return self::searchAdmin('', ['status' => 1])
            ->with('section')
            ->whereHas('section', function ($query) {
                $query->where('status', 1)
                    ->whereHas('group', function ($query) {
                        $query->where('status', 1);
                    });
            })
            ->get()
            ->groupBy('section.name');
    }

    public static function validAssignableIds(array $permissionIds): array
    {
        return self::query()
            ->whereIn('id', $permissionIds)
            ->where('status', 1)
            ->whereHas('section', function ($query) {
                $query->where('status', 1)
                    ->whereHas('group', function ($query) {
                        $query->where('status', 1);
                    });
            })
            ->pluck('id')
            ->all();
    }
}
