<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,

            GroupAdminSeeder::class,

            SectionAdminSeeder::class,

            PermissionAdminSeeder::class,

            GroupEmpresaSeeder::class,

            SectionEmpresaSeeder::class,

            PermissionEmpresaSeeder::class,

            EstadoSeeder::class, 
            
            TasaServicioSeeder::class,

            AdminDemoSeeder::class,
        ]);
    }
}

