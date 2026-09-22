<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class PermissionAdmin extends ModelHelper
{
    protected $table = 'permissions_admin';

    public $timestamps = false;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['status' => 'boolean'];
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(SectionAdmin::class, 'section_id');
    }

    public function admins(): BelongsToMany
    {
        return $this->belongsToMany(Admin::class, 'permission_admin_admin', 'permission_id', 'admin_id')->withPivot('status');
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
}
