<?php

use App\Models\Empresa;
use App\Models\OrdenCobro;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

require dirname(__DIR__).'/vendor/autoload.php';

$database = sys_get_temp_dir().'/parada-cms-ordenes-cobro.sqlite';
@unlink($database);
touch($database);

putenv('DB_CONNECTION=sqlite');
putenv('DB_DATABASE='.$database);
putenv('DEMO_TOTAL_AGENCIAS=2');
putenv('DEMO_TOTAL_CONDUCTORES=2');
putenv('DEMO_FECHA_DESDE=2026-09-01');
putenv('DEMO_FECHA_HASTA=2026-09-27');

$app = require dirname(__DIR__).'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

config([
    'database.default' => 'sqlite',
    'database.connections.sqlite.database' => $database,
    'cache.default' => 'array',
    'session.driver' => 'array',
]);

DB::purge('sqlite');
$exitCode = Artisan::call('migrate:fresh', ['--seed' => true, '--force' => true]);

if ($exitCode !== 0) {
    throw new RuntimeException(Artisan::output());
}

$empresasInvalidas = Empresa::query()
    ->where(function ($query) {
        $query->where('dia_corte', '!=', 7);
        $query->orWhere('dia_vencimiento', '!=', 5);
    })
    ->count();

assert($empresasInvalidas === 0, 'Todas las empresas deben cortar el domingo y cerrar el viernes.');
echo 'Empresas: '.DB::table('empresas')->count().', reservas: '.DB::table('reservas')->count().', contratos ellos: '.Empresa::query()->where('tipo_contrato', Empresa::CONTRATO_ELLOS_RECIBEN)->count().PHP_EOL;
assert(OrdenCobro::query()->count() > 0, 'No se generaron órdenes de cobro.');
assert(OrdenCobro::query()->where('cantidad_reservas', '<=', 0)->doesntExist(), 'Hay órdenes sin reservas.');
assert(OrdenCobro::query()->where('total', '<=', 0)->doesntExist(), 'Hay órdenes sin tasa de servicio.');

foreach (OrdenCobro::query()->get() as $orden) {
    assert($orden->periodo_desde->isSunday(), 'El período no comienza el domingo.');
    assert($orden->fecha_emision->isSunday(), 'La orden no se emitió el domingo.');
    assert($orden->fecha_vencimiento->isFriday(), 'La orden no cierra el viernes.');
    assert(count($orden->reservas_incluidas) === $orden->cantidad_reservas, 'El resumen no coincide con la cantidad.');
}

echo 'OK: '.OrdenCobro::query()->count().' órdenes de cobro verificadas.'.PHP_EOL;

@unlink($database);
