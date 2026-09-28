<?php
namespace Database\Seeders;
use App\Models\TasaServicio;
use Illuminate\Database\Seeder;
class TasaServicioSeeder extends Seeder
{
    public function run(): void
    {
        if (TasaServicio::exists()) return;
        TasaServicio::create(['monto_minimo'=>'0.00','monto_maximo'=>'2000.00','cantidad'=>'1.25','estatus'=>1]);
    }
}
