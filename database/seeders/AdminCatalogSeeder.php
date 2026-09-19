<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AdminCatalogSeeder extends Seeder
{
    public function run(): void
    {
        $sections = json_decode(Storage::disk('json')->get('sections_admin.json'), true, 512, JSON_THROW_ON_ERROR);
        $permissions = json_decode(Storage::disk('json')->get('permissions_admin.json'), true, 512, JSON_THROW_ON_ERROR);

        DB::transaction(function () use ($sections, $permissions) {
            DB::table('groups_admin')->orderBy('id')->lockForUpdate()->get();

            // Conservar asignaciones por sección/acción cuando cambien los IDs del catálogo.
            $assignments = DB::table('permission_admin_admin as assignments')
                ->join('permissions_admin as permissions', 'permissions.id', '=', 'assignments.permission_id')
                ->join('sections_admin as sections', 'sections.id', '=', 'permissions.section_id')
                ->select('assignments.id', 'assignments.admin_id', 'assignments.status', 'sections.url as section', 'permissions.url as action')
                ->lockForUpdate()->get();

            DB::table('permission_admin_admin')->delete();
            DB::table('permissions_admin')->delete();
            DB::table('sections_admin')->delete();

            $sectionNames = [];

            foreach ($sections as $section) {
                $id = $section['attributes']['id'];
                DB::table('sections_admin')->insert(['id' => $id] + $section['values']);
                $sectionNames[$id] = $section['values']['url'];
            }

            $permissionIds = [];

            foreach ($permissions as $permission) {
                $id = $permission['attributes']['id'];
                $values = $permission['values'];
                DB::table('permissions_admin')->insert(['id' => $id] + $values);
                $permissionIds[$sectionNames[$values['section_id']] . '.' . $values['url']] = $id;
            }

            foreach ($assignments as $assignment) {
                $permissionId = $permissionIds[$assignment->section . '.' . $assignment->action] ?? null;

                if ($permissionId !== null) {
                    DB::table('permission_admin_admin')->insert([
                        'id' => $assignment->id,
                        'admin_id' => $assignment->admin_id,
                        'permission_id' => $permissionId,
                        'status' => $assignment->status,
                    ]);
                }
            }
        });
    }
}
