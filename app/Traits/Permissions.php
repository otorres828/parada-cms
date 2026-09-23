<?php

namespace App\Traits;

use App\Models\Admin;
use App\Services\Admin\Access;
use Illuminate\Support\Facades\Auth;

trait Permissions
{
    public bool $canAdd = false;

    public bool $canEdit = false;

    public bool $canDelete = false;

    public bool $canDownload = false;

    public bool $canDetail  = false;

    public bool $canPermissions = false;

    public bool $canReview = false;

    public function checkPermissions(string $module, array $permissions = []): void
    {

        Access::authorize($module, 'list');
        
        $admin = Admin::find(Auth::guard('admin')->id());

        $checks = [];

        $permissions = array_merge(['add', 'edit', 'delete', 'download'], $permissions);

        foreach ($permissions as $permission) {
            $checks['can' . ucfirst($permission)] = [$module, $permission];
        }

        $results = $admin?->checkPermissionsBatch($checks) ?? [];

        foreach ($results as $property => $allowed) {
            $this->{$property} = $allowed;
        }
    }
}
