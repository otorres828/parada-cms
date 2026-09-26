<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\PermissionAdmin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class AdminRolesSeeder extends Seeder
{
    public function run(): void
    {
        $profiles = [
            'abogado' => [
                'name' => 'Abogado',
                'permissions' => [
                    'empresas' => ['list', 'detail'],
                    'legales' => ['list', 'detail', 'add', 'file'],
                ],
            ],
            'contador' => [
                'name' => 'Contador',
                'permissions' => [
                    'dashboard' => ['list'],
                    'reservas' => ['list', 'detail'],
                    'pasajes' => ['list', 'detail'],
                    'reembolsos' => ['list', 'detail'],
                    'reportes' => ['list-sales', 'list-companies', 'list-exchange-rates', 'update-exchange-rates'],
                ],
            ],
            'ventas' => [
                'name' => 'Ventas',
                'permissions' => [
                    'empresas' => ['list', 'detail'],
                    'clientes' => ['list', 'detail', 'edit'],
                    'reservas' => ['list', 'detail'],
                    'pasajes' => ['list', 'detail'],
                    'reembolsos' => ['list', 'detail'],
                    'viajes' => ['list', 'detail'],
                    'programaciones' => ['list', 'passengers'],
                    'autobuses' => ['list', 'detail'],
                    'cupones' => ['list', 'detail'],
                ],
            ],
        ];

        $credentials = DB::transaction(function () use ($profiles) {
            $permissions = PermissionAdmin::where('status', 1)
                ->with('section')
                ->whereHas('section', fn ($query) => $query->where('status', 1)
                    ->whereHas('group', fn ($group) => $group->where('status', 1)))
                ->get()
                ->keyBy(fn ($permission) => $permission->section->url.'/'.$permission->url);

            $legacy = Admin::where('email', 'demo-operador@example.test')->first();
            $credentials = [];

            foreach ($profiles as $username => $profile) {
                $email = $username.'@pidetuparada.com';
                $admin = Admin::where('email', $email)->first();
                $needsPassword = ! $admin;

                // Reutilizar el antiguo actor conserva las referencias de los datos demo.
                $admin ??= $username === 'contador' && $legacy ? $legacy : new Admin;
                $admin->fill([
                    'name' => $profile['name'],
                    'email' => $email,
                    'username' => $username,
                    'level' => Admin::ADMIN,
                    'status' => Admin::ACTIVO,
                ]);

                if ($needsPassword) {
                    $password = Str::password(20);
                    $admin->password = $password;
                    $credentials[] = [$email, $username, $password];
                }

                $admin->save();
                $assigned = [];

                foreach ($profile['permissions'] as $module => $actions) {
                    foreach ($actions as $action) {
                        $key = $module.'/'.$action;
                        $permission = $permissions->get($key);

                        if (! $permission) {
                            throw new RuntimeException("Falta el permiso activo {$key}. Ejecuta GroupSectionPermissionAdminSeeder primero.");
                        }

                        $assigned[$permission->id] = ['status' => 1];
                    }
                }

                $admin->permissions()->sync($assigned);
            }

            // Si ya existían ambas cuentas, retirar el demo sin borrar su historial.
            if ($legacy && $legacy->email === 'demo-operador@example.test') {
                $legacy->permissions()->detach();
                $legacy->update(['status' => Admin::ELIMINADO]);
            }

            return $credentials;
        });

        if ($credentials) {
            $this->command?->info('Contraseñas iniciales generadas. Las cuentas existentes conservan su contraseña.');
            $this->command?->table(['Correo', 'Usuario', 'Contraseña inicial'], $credentials);
        }
    }
}
