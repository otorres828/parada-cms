<?php

namespace App\Traits;

use App\Services\Admin\Access;

trait Permissions
{
    public bool $canAdd = false;

    public bool $canEdit = false;

    public bool $canDelete = false;

    public bool $canDownload = false;

    public bool $canDetail  = false;

    public function checkPermissions(string $module, array $permissions = []): void
    {
        Access::authorize($module, 'list');
        $this->canAdd = Access::allows($module, 'add');
        $this->canEdit = Access::allows($module, 'edit');
        $this->canDelete = Access::allows($module, 'delete');
        $this->canDownload = Access::allows($module, 'download');

        foreach($permissions as $permission) {
            $this->{'can' . ucfirst($permission)} = Access::allows($module, $permission);
        }
    }
}
