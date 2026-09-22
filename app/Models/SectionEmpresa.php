<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SectionEmpresa extends ModelHelper
{
    protected $table = 'sections_empresa';

    public $timestamps = false;

    protected $fillable = [
        0 => 'group_id',
        1 => 'name',
        2 => 'url',
        3 => 'status',
    ];

    protected function casts(): array
    {
        return ['status' => 'boolean'];
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(GroupEmpresa::class, 'group_id');
    }

    public function permissions(): HasMany
    {
        return $this->hasMany(PermissionEmpresa::class, 'section_id');
    }

    public static function searchAdmin(string $search = '', array $filters = []): Builder
    {
        $query = self::query()->with([
            0 => 'permissions',
            1 => 'group',
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
}
