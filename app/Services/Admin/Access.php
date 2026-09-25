<?php

namespace App\Services\Admin;

use App\Models\Admin;
use Illuminate\Support\Facades\DB;

class Access
{
    public static function allows(string $module, string $action): bool
    {
        $admin = Admin::find(auth('admin')->id());

        return $admin?->hasPermission($module, $action) ?? false;
    }

    public static function authorize(string $module, string $action): void
    {
        abort_unless(self::allows($module, $action), 403, 'No tienes permiso para realizar esta acción.');
    }

    public static function permissions(Admin $admin)
    {
        return DB::table('permissions_admin')
            ->join('sections_admin', 'sections_admin.id', '=', 'permissions_admin.section_id')
            ->join('groups_admin', 'groups_admin.id', '=', 'sections_admin.group_id')
            ->join('permission_admin_admin', 'permission_admin_admin.permission_id', '=', 'permissions_admin.id')
            ->where('permission_admin_admin.admin_id', $admin->id)->where('permission_admin_admin.status', 1)
            ->where('permissions_admin.status', 1)->where('sections_admin.status', 1)->where('groups_admin.status', 1)
            ->select('sections_admin.url as section_url', 'permissions_admin.url as permission_url')->get();
    }

}
