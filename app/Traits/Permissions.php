<?php

namespace App\Traits;

use App\Models\Admin;
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
        $admin = Admin::find(Auth::guard('admin')->id());

        $checks = [
            'list' => [$module, 'list']
        ];

        $permissions = array_merge(['add', 'edit', 'delete', 'download'], $permissions);
        
        foreach ($permissions as $permission) {
            $checks['can' . ucfirst($permission)] = [$module, $permission];
        }

        $results = $admin?->checkPermissionsBatch($checks) ?? [];

        abort_unless($results['list'] ?? false, 403, 'No tienes permiso para realizar esta acción.');

        unset($results['list']);

        foreach ($results as $property => $allowed) {
            $this->{$property} = $allowed;
        }
    }
}
