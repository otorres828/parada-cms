<?php

namespace App\Traits;

use App\Models\UsuarioEmpresa;
use Illuminate\Support\Facades\Auth;

trait PermissionsEmpresa
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
        $usuario = UsuarioEmpresa::find(Auth::guard('empresa')->id());

        $checks = [];

        $permissions = array_merge(['add', 'edit', 'delete', 'download'], $permissions);

        foreach ($permissions as $permission) {
            $checks['can' . ucfirst($permission)] = [$module, $permission];
        }

        $results = $usuario?->checkPermissionsBatch($checks) ?? [];

        foreach ($results as $property => $allowed) {
            $this->{$property} = $allowed;
        }
    }
}
