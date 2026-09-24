<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GroupAdmin extends ModelHelper
{
    protected $table = 'groups_admin';

    public $timestamps = false;

    protected $fillable = [
        0 => 'name',
        1 => 'url',
        2 => 'icon',
        3 => 'status',
    ];

    protected function casts(): array
    {
        return ['status' => 'boolean'];
    }

    public function sections(): HasMany
    {
        return $this->hasMany(SectionAdmin::class, 'group_id');
    }

    public static function searchAdmin(string $search = '', array $filters = []): Builder
    {
        $query = self::query()->with([
            0 => 'sections.permissions',
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

    public static function activeForAdminAssignment(): Collection
    {
        return self::query()
            ->where('status', 1)
            ->with([
                'sections' => function ($query) {
                    $query->where('status', 1)
                        ->where('url', '!=', 'admins');
                },
                'sections.permissions' => function ($query) {
                    $query->where('status', 1);
                },
            ])
            ->orderBy('id')
            ->get();
    }
}
