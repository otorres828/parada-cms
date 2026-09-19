<?php
namespace Database\Seeders;
use App\Models\TasaServicio;
use Illuminate\Database\Seeder;
class TasaServicioSeeder extends Seeder
{
    public function run(): void
    {
        if (TasaServicio::exists()) return;
        TasaServicio::create(['monto_minimo'=>'0.00','monto_maximo'=>'20.00','cantidad'=>'1.00','estatus'=>1]);
        TasaServicio::create(['monto_minimo'=>'20.01','monto_maximo'=>null,'cantidad'=>'1.50','estatus'=>1]);
    }
}
