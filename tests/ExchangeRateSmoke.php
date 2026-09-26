<?php

use App\Models\TipoCambio;
use Database\Seeders\GroupSectionPermissionAdminSeeder;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();
config([
    'database.default' => 'sqlite',
    'database.connections.sqlite.database' => ':memory:',
    'cache.default' => 'array',
    'session.driver' => 'array',
]);
DB::purge('sqlite');
Artisan::call('migrate', ['--force' => true]);

Http::fakeSequence()
    ->push([
        'USD' => 500.4699,
        'EUR' => 589.2799,
    ])
    ->push(['USD' => 500.4606]);

if (Artisan::call('tipos-cambio:actualizar') !== 0) {
    throw new RuntimeException('El comando no guardó una respuesta válida.');
}

$tipoCambio = TipoCambio::firstOrFail();
if ($tipoCambio->valor_usd !== '500.46' || $tipoCambio->valor_eur !== '589.27') {
    throw new RuntimeException("Las tasas guardadas no coinciden con la respuesta de la API: USD {$tipoCambio->valor_usd}, EUR {$tipoCambio->valor_eur}.");
}

TipoCambio::query()->delete();

if (Artisan::call('tipos-cambio:actualizar') === 0 || TipoCambio::query()->exists()) {
    throw new RuntimeException('El comando aceptó una respuesta incompleta.');
}

Artisan::call('db:seed', [
    '--class' => GroupSectionPermissionAdminSeeder::class,
    '--force' => true,
]);

$permisos = DB::table('permissions_admin')
    ->join('sections_admin', 'sections_admin.id', '=', 'permissions_admin.section_id')
    ->where('sections_admin.url', 'reportes')
    ->whereIn('permissions_admin.url', ['list-exchange-rates', 'update-exchange-rates'])
    ->count();

if ($permisos !== 2) {
    throw new RuntimeException('No se registraron los permisos del módulo de tasas de cambio.');
}

echo "OK: consulta, validación, persistencia y permisos de tasas de cambio.\n";
