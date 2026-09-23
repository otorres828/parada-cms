<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\ModelHelper;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear Administradores de prueba
        Admin::updateOrCreate([
            'name' => 'Oliver Torres',
            'email' => 'oliver@parada.com',
            'username' => 'oliver'
        ], [
            'password' => Hash::make(26269828),
            'status' => ModelHelper::ESTADO_ACTIVE,
            'level'=> 1
        ]);

        Admin::updateOrCreate([
            'name' => 'Ancarlys Rosas',
            'email' => 'ancarlys@parada.com',
            'username' => 'ancarlys'
        ], [
            'password' => Hash::make(26269828),
            'status' => ModelHelper::ESTADO_ACTIVE,
            'level'=> 1
        ]);

        Admin::updateOrCreate([
            'name' => 'Cesar Sotillo',
            'email' => 'cesar@parada.com',
            'username' => 'cesar'
        ], [
            'password' => Hash::make(26269828),
            'status' => ModelHelper::ESTADO_ACTIVE,
            'level'=> 1
        ]);
    }
}
