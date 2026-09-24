<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SectionAdmin extends ModelHelper
{
    protected $table = 'sections_admin';

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
        return $this->belongsTo(GroupAdmin::class, 'group_id');
    }

    public function permissions(): HasMany
    {
        return $this->hasMany(PermissionAdmin::class, 'section_id');
    }

    public static function searchAdmin(string $search = '', array $filters = []): Builder
    {
        $query = self::query()->with([
            0 => 'permissions',
            1 => 'group',
        ]);

        if ($search !== '') {
            $query->where(function ($query) use ($search) {
                return $query->where('name', 'like', '%'.$search.'%')->orWhere('url', 'like', '%'.$search.'%');
            });
        }

        $status = $filters['status'] ?? null;

        if ($status !== null && $status !== '') {
            $query->where('status', $status);
        } else {
            $query->where('status', '!=', self::ESTADO_DELETE);
        }

        return $query;
    }
}
