<?php

use App\Models\Cupon;
use App\Models\Empresa;
use App\Models\Pago;
use App\Models\Pasaje;
use App\Models\Reserva;
use App\Models\TasaServicio;
use App\Models\Viajero;
use Carbon\Carbon;
use Database\Seeders\AdminDemoSeeder;
use Database\Seeders\GroupAdminSeeder;
use Database\Seeders\TasaServicioSeeder;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;

// Base temporal independiente; nunca ejecuta migraciones sobre la conexión real.
require dirname(__DIR__).'/vendor/autoload.php';
$app = require dirname(__DIR__).'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

function verificarDemo(bool $correcto, string $mensaje): void
{
    if (!$correcto) {
        throw new RuntimeException($mensaje);
    }
}

try {
    Carbon::setTestNow(Carbon::parse('2026-10-01 12:00:00'));
    config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:', 'hashing.bcrypt.rounds' => 4]);
    DB::purge('sqlite');
    foreach (glob(dirname(__DIR__).'/database/migrations/*.php') as $file) {
        (require $file)->up();
    }
    (new GroupAdminSeeder)->run();
    (new TasaServicioSeeder)->run();
    (new AdminDemoSeeder)->run();

    verificarDemo(now()->format('Y-m-d H:i:s') === '2026-10-01 12:00:00', 'El reloj simulado no se restauró.');
    verificarDemo(Reserva::count() === 72 && Pasaje::count() === 72, 'Cantidad de compras incorrecta.');
    verificarDemo(Reserva::where('estado_pago', Reserva::ESTADO_PAGO_PAGADO)->count() === 54, 'Reservas pagadas.');
    verificarDemo(Reserva::where('estado_pago', Reserva::ESTADO_PAGO_PENDIENTE)->count() === 12, 'Reservas pendientes.');
    verificarDemo(Reserva::where('estado_pago', Reserva::ESTADO_PAGO_CANCELADO)->count() === 6, 'Reservas canceladas.');
    verificarDemo(Cupon::where('redimido', true)->count() === 3, 'Cupones no redimidos por el servicio.');
    verificarDemo(Empresa::where('estatus', false)->count() === 1, 'Empresa inactiva de demostración.');

    foreach (Reserva::with('pasajes.viajero', 'pagos')->get() as $reserva) {
        if (in_array($reserva->estado_pago, [Reserva::ESTADO_PAGO_PENDIENTE, Reserva::ESTADO_PAGO_PAGADO], true)) {
            verificarDemo($reserva->fecha_expiracion === null, 'Un pago reportado no debe expirar.');
        } else {
            verificarDemo($reserva->fecha_expiracion->equalTo($reserva->fecha_compra->copy()->addMinutes(20)), 'Bloqueo inicial distinto de 20 minutos.');
        }
        $total = '0.00';
        $tasas = '0.00';
        foreach ($reserva->pasajes as $pasaje) {
            verificarDemo($pasaje->viajero !== null && $pasaje->tipo_servicio !== null && $pasaje->tasa_monto_minimo !== null, 'Checkout incompleto.');
            verificarDemo($pasaje->viajero->usuario_id == $reserva->usuario_id, 'Pasajero de otro cliente.');
            $regla = new TasaServicio(['tipo_servicio' => $pasaje->tipo_servicio, 'cantidad' => $pasaje->valor_servicio]);
            verificarDemo($regla->calcular($pasaje->precio_final) === $pasaje->tasa_servicio, 'Tasa incorrecta.');
            $tasas = bcadd($tasas, $pasaje->tasa_servicio, 2);
            $total = bcadd($total, bcadd($pasaje->precio_final, $pasaje->tasa_servicio, 2), 2);
        }
        verificarDemo($reserva->monto_total === $total && $reserva->tasa_servicio === $tasas, 'Totales incorrectos.');
        foreach ($reserva->pagos as $pago) {
            verificarDemo($pago->monto === $total && $pago->neto_empresa === bcsub($reserva->monto_pasajes, $reserva->descuento_aplicado, 2), 'Conciliación incorrecta tras aplicar cupón.');
        }
    }

    $antes = [Reserva::orderBy('id')->get()->toJson(), Pasaje::orderBy('id')->get()->toJson(), Viajero::count(), Pago::count()];
    (new AdminDemoSeeder)->run();
    $despues = [Reserva::orderBy('id')->get()->toJson(), Pasaje::orderBy('id')->get()->toJson(), Viajero::count(), Pago::count()];
    verificarDemo($antes === $despues, 'Repetir el seeder modificó o duplicó compras.');
    verificarDemo(now()->format('Y-m-d H:i:s') === '2026-10-01 12:00:00', 'Reloj alterado al repetir.');
    echo "OK: 72 compras por ReservaService, 3 cupones, bloqueo de 20 minutos, pagos, reloj restaurado y repetición sin cambios.\n";
} catch (Throwable $e) {
    fwrite(STDERR, get_class($e).': '.$e->getMessage()."\n");
    exit(1);
} finally {
    Carbon::setTestNow();
}
