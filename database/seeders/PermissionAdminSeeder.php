<?php

namespace Database\Seeders;

use App\Models\PermissionAdmin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class PermissionAdminSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = json_decode(Storage::disk('json')->get('permissions_admin.json'), true);
        foreach ($permissions as $permission) {
            PermissionAdmin::query()->updateOrCreate($permission['attributes'], $permission['values']);
        }

    }
}
