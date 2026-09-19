<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GroupEmpresa extends ModelHelper
{
    protected $table = 'groups_empresa';

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
        return $this->hasMany(SectionEmpresa::class, 'group_id');
    }

    public static function searchAdmin(string $search = '', array $filters = []): Builder
    {
        $query = self::query()->with([
            0 => 'sections.permissions',
        ]);

        if ($search !== '') {
            $query->where(fn ($q) => $q->where('name', 'like', '%' . $search . '%')->orWhere('url', 'like', '%' . $search . '%'));
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query;
    }
}
