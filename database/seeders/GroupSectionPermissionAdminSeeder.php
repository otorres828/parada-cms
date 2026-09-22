<?php

namespace Database\Seeders;

use App\Models\GroupAdmin;
use App\Models\PermissionAdmin;
use App\Models\SectionAdmin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class GroupSectionPermissionAdminSeeder extends Seeder
{
    public function run(): void
    {
        $groups = json_decode(Storage::disk('json')->get('grupo_seccion_permiso_admin.json'), true, 512, JSON_THROW_ON_ERROR);

        Validator::make(['groups' => $groups], [
            'groups' => 'present|array',
            'groups.*.name' => 'required|string|max:100',
            'groups.*.url' => 'required|string|max:100|distinct',
            'groups.*.icon' => 'required|string|max:100',
            'groups.*.status' => 'required|boolean',
            'groups.*.sections' => 'present|array',
            'groups.*.sections.*.name' => 'required|string|max:100',
            'groups.*.sections.*.url' => 'required|string|max:100|distinct',
            'groups.*.sections.*.status' => 'required|boolean',
            'groups.*.sections.*.permissions' => 'present|array',
            'groups.*.sections.*.permissions.*.name' => 'required|string|max:100',
            'groups.*.sections.*.permissions.*.url' => 'required|string|max:100',
            'groups.*.sections.*.permissions.*.status' => 'required|boolean',
        ])->validate();

        DB::transaction(function () use ($groups) {
            foreach ($groups as $group) {
                $groupRecord = GroupAdmin::updateOrCreate(['url' => $group['url']], [
                    'name' => $group['name'],
                    'icon' => $group['icon'],
                    'status' => $group['status'],
                ]);

                foreach ($group['sections'] as $section) {
                    Validator::make(['permissions' => $section['permissions']], [
                        'permissions.*.url' => 'distinct',
                    ])->validate();

                    $sectionRecord = SectionAdmin::updateOrCreate(['url' => $section['url']], [
                        'group_id' => $groupRecord->id,
                        'name' => $section['name'],
                        'status' => $section['status'],
                    ]);

                    foreach ($section['permissions'] as $permission) {
                        PermissionAdmin::updateOrCreate([
                            'section_id' => $sectionRecord->id,
                            'url' => $permission['url'],
                        ], [
                            'name' => $permission['name'],
                            'status' => $permission['status'],
                        ]);
                    }
                }
            }
        });
    }
}
