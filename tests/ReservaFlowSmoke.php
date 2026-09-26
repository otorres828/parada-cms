<?php

use App\Models\Admin;
use App\Models\Autobus;
use App\Models\ConfiguracionCupon;
use App\Models\DatoBancario;
use App\Models\Empresa;
use App\Models\Estado;
use App\Models\Programacion;
use App\Models\ProgramacionTramoPrecio;
use App\Models\Reserva;
use App\Models\TasaServicio;
use App\Models\Terminal;
use App\Models\TipoCambio;
use App\Models\User;
use App\Models\Viaje;
use App\Services\CuponService;
use App\Services\Empresa\ReembolsoService;
use App\Services\PagoReservaService;
use App\Services\ReservaService;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();
config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:', 'cache.default' => 'array', 'session.driver' => 'array']);
DB::purge('sqlite');
Artisan::call('migrate', ['--force' => true]);

$check = function ($condition) {
    if (! $condition) {
        throw new RuntimeException('Falló una comprobación');
    }
};
$reject = function ($callback, $class) {
    try {
        $callback();
    } catch (Throwable $e) {
        if ($e instanceof $class) {
            return;
        } throw $e;
    } throw new RuntimeException('La operación debió rechazarse');
};
$cliente = User::create(['name' => 'Ana', 'lastname' => 'Perez', 'email' => 'ana@test.test']);
$otro = User::create(['name' => 'Otro', 'lastname' => 'Perez', 'email' => 'otro@test.test']);
$empresa = Empresa::create(['nombre' => 'Empresa', 'rif' => 'J1', 'telefono' => '123', 'email' => 'empresa@test.test', 'estatus' => 1]);
$estado = Estado::create(['nombre' => 'Estado']);
$terminales = [];
foreach (['Origen', 'Destino'] as $nombre) {
    $terminales[] = Terminal::create(['estado_id' => $estado->id, 'nombre' => $nombre, 'direccion' => 'Calle', 'latitud' => 0, 'longitud' => 0]);
}
$bus = Autobus::create(['empresa_id' => $empresa->id, 'modelo' => 'Bus', 'tipo_asiento' => 'Normal', 'total_asientos' => 2, 'estatus' => 1, 'es_plantilla' => false]);
$viaje = Viaje::create(['empresa_id' => $empresa->id, 'origen_terminal_id' => $terminales[0]->id, 'destino_terminal_id' => $terminales[1]->id, 'duracion_estimada' => '01:00:00', 'estatus' => 1]);
$programacion = Programacion::create(['viaje_id' => $viaje->id, 'autobus_id' => $bus->id, 'fecha_salida' => today()->addDay(), 'hora_salida' => '18:00:00', 'asientos_totales' => 2, 'estatus' => 1]);
$tarifa = ProgramacionTramoPrecio::create(['programacion_id' => $programacion->id, 'origen_terminal_id' => $terminales[0]->id, 'destino_terminal_id' => $terminales[1]->id, 'precio' => '10.00']);
TasaServicio::create(['monto_minimo' => 0, 'monto_maximo' => null, 'cantidad' => 1, 'tipo_servicio' => 1, 'estatus' => 1]);
$tipoCambio = TipoCambio::create(['valor_usd' => '500.00000000', 'valor_eur' => '590.00000000', 'valor' => 1]);
$banco = DatoBancario::create(['empresa_id' => $empresa->id, 'tipo' => 1, 'banco' => 'Banco', 'nombre_titular' => 'Empresa', 'tipo_titular' => 'juridico', 'numero_documento' => 'J1', 'numero_cuenta_telefono' => '123', 'estatus' => 1]);
$pasajero = ['nombre' => 'Ana', 'apellido' => 'Perez', 'fecha_nacimiento' => '1990-01-01', 'tipo_pasajero' => 'adulto'];
// El servicio funciona sin sesión: el middleware y el consumidor seleccionan al cliente.
$r = ReservaService::aplicarReserva($cliente, $tarifa->id);
$check($r->pasajes->isEmpty() && $r->monto_total === '11.00' && $r->tipos_cambios_id === $tipoCambio->id && $r->monto_total_bolivares === '5500.00');
$reject(fn () => ReservaService::agregarPasajero($otro, $r->id, $pasajero), ModelNotFoundException::class);
$r = ReservaService::agregarPasajero($cliente, $r->id, $pasajero);
ConfiguracionCupon::create(['nombre_campana' => 'Descuento', 'tipo_cupon' => ConfiguracionCupon::TIPO_PERSONALIZADO, 'codigo_personalizado' => 'TEST', 'modalidad' => ConfiguracionCupon::MODALIDAD_PRIMERA_COMPRA, 'aplica_en' => ConfiguracionCupon::APLICA_EN_PASAJES, 'cantidad_generar' => 10, 'tipo_descuento' => 'monto_fijo', 'monto_descuento' => 1, 'fecha_inicio' => now()->subDay(), 'fecha_fin' => now()->addDay(), 'estatus' => 1]);
app(CuponService::class)->aplicarCupon($r, 'TEST');
$r = ReservaService::agregarPasajero($cliente, $r->id, $pasajero);
$check($r->monto_total === '20.00' && $r->descuento_aplicado === '2.00');
$reject(fn () => ReservaService::agregarPasajero($cliente, $r->id, $pasajero), ValidationException::class);
$r = ReservaService::removerPasajero($cliente, $r->id, $r->pasajes->last()->id);
$check($r->monto_total === '10.00');
$r = ReservaService::prepararResumen($cliente, $r->id);
$r = PagoReservaService::pasarAPendiente($cliente, $r->id, $banco->id, 'REF', now()->toDateTimeString());
$check($r->fecha_expiracion === null);
PagoReservaService::pasarAPendiente($cliente, $r->id, $banco->id, 'REF', now()->toDateTimeString());
$check($r->pago()->count() === 1);
$reject(fn () => PagoReservaService::confirmarPago($r->id, '99.00'), ValidationException::class);
$r = PagoReservaService::confirmarPago($r->id, '10.00');
$check($r->estado_pago === Reserva::ESTADO_PAGO_PAGADO);
$reject(fn () => ReservaService::cancelarReserva($cliente, $r->id), ValidationException::class);
$vac = ReservaService::aplicarReserva($otro, $tarifa->id);
$vac = ReservaService::agregarPasajero($otro, $vac->id, $pasajero);
$vac = ReservaService::removerPasajero($otro, $vac->id, $vac->pasajes->first()->id);
$check($vac->pasajes->isEmpty() && $vac->monto_total === '11.00');
$vac = ReservaService::agregarPasajero($otro, $vac->id, $pasajero);
$vac->update(['fecha_expiracion' => now()->subSecond()]);
$reject(fn () => PagoReservaService::pasarAPendiente($otro, $vac->id, $banco->id, 'REF2', now()->toDateTimeString()), ValidationException::class);
echo "OK: flujo, cliente seleccionado, propiedad, cupos, cupones, tasas, pagos, caducidad.\n";
$admin = Admin::create(['name' => 'Admin', 'username' => 'admin', 'email' => 'admin@test.test', 'password' => 'test']);
auth('admin')->setUser($admin);
$antes = $r->fresh()->only(['monto_pasajes', 'descuento_aplicado', 'tasa_servicio', 'monto_total', 'cupon_id']);
$reembolso = ReembolsoService::crear(['pago_reserva_id' => $r->pago->id, 'motivo' => 'Reembolso de prueba']);
ReembolsoService::revisar($reembolso->id, 'aprobado', 'Aprobado', null, null);
ReembolsoService::revisar($reembolso->id, 'pagado', 'Pagado', 'REEMBOLSO', 'comprobante.pdf');
$check($r->fresh()->estado_pago === Reserva::ESTADO_PAGO_REEMBOLSADO);
$check($r->fresh()->only(array_keys($antes)) === $antes);
echo "OK: reembolso conserva cupón, tasas e importes históricos.\n";

// Rutas de cupón que reciben una reserva ya bloqueada por conReserva.
$nueva = ReservaService::aplicarReserva($otro, $tarifa->id);
$nueva = ReservaService::agregarPasajero($otro, $nueva->id, $pasajero);
$nueva = app(CuponService::class)->aplicarCupon($nueva, 'TEST');
$nueva = ReservaService::removerPasajero($otro, $nueva->id, $nueva->pasajes->first()->id);
$check($nueva->cupon_id === null && $nueva->monto_total === '11.00');
$nueva = ReservaService::agregarPasajero($otro, $nueva->id, $pasajero);
app(CuponService::class)->aplicarCupon($nueva, 'TEST');
$nueva = ReservaService::cancelarReserva($otro, $nueva->id);
$check($nueva->cupon_id === null && $nueva->estado_pago === Reserva::ESTADO_PAGO_CANCELADO);
$nueva = ReservaService::aplicarReserva($otro, $tarifa->id);
$nueva = ReservaService::agregarPasajero($otro, $nueva->id, $pasajero);
app(CuponService::class)->aplicarCupon($nueva, 'TEST');
PagoReservaService::pasarAPendiente($otro, $nueva->id, $banco->id, 'REF-FALLIDA', now()->toDateTimeString());
$nueva = PagoReservaService::marcarPagoFallido($nueva->id);
$check($nueva->cupon_id === null && $nueva->estado_pago === Reserva::ESTADO_PAGO_FALLIDO);
echo "OK: liberación de cupón al retirar último pasajero, cancelar y rechazar pago.\n";
