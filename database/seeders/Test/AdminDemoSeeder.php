<?php

namespace Database\Seeders\Test;

use Illuminate\Database\Seeder;

class AdminDemoSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            CatalogosDemoSeeder::class,
            EmpresasDemoSeeder::class,
            OperacionHistoricaDemoSeeder::class,
            OrdenesCobroDemoSeeder::class,
        ]);
    }
}
