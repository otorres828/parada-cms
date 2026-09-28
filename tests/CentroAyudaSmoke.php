<?php

use App\Models\CategoriaPreguntaFrecuente;
use App\Models\PreguntaFrecuente;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

require dirname(__DIR__).'/vendor/autoload.php';

$database = sys_get_temp_dir().'/parada-cms-centro-ayuda.sqlite';
@unlink($database);
touch($database);

putenv('DB_CONNECTION=sqlite');
putenv('DB_DATABASE='.$database);
putenv('DEMO_TOTAL_AGENCIAS=1');
putenv('DEMO_TOTAL_CONDUCTORES=1');
putenv('DEMO_FECHA_DESDE=2026-09-27');
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

assert(CategoriaPreguntaFrecuente::query()->count() === 5);
assert(PreguntaFrecuente::query()->count() === 7);
assert(PreguntaFrecuente::query()->whereNull('categoria_pregunta_frecuente_id')->doesntExist());
assert(CategoriaPreguntaFrecuente::searchAdmin('', ['estatus' => 1])->with('preguntas')->get()->every(
    fn ($categoria) => $categoria->preguntas->isNotEmpty(),
));
assert(PreguntaFrecuente::searchAdmin('transferencia')->count() >= 1);
assert(Route::has('admin.preguntas-frecuentes.categorias.list'));
assert(Route::has('admin.preguntas-frecuentes.categorias.add'));
assert(Route::has('admin.preguntas-frecuentes.categorias.edit'));

echo 'OK: centro de ayuda verificado.'.PHP_EOL;

@unlink($database);
