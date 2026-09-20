<?php

use App\Models\ConfiguracionCupon;
use App\Models\Cupon;
use App\Models\Programacion;
use App\Models\ProgramacionTramoPrecio;
use App\Models\Reserva;
use App\Models\TasaServicio;
use App\Models\User;
use App\Models\Viajero;
use App\Services\ReservaService;
use Carbon\Carbon;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

// Ejecutar con PHP 8.3 y pdo_sqlite. Siempre usa SQLite en memoria; no modifica la base real.
require dirname(__DIR__).'/vendor/autoload.php';
$app = require dirname(__DIR__).'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

function comprobar(bool $ok, string $mensaje): void
{
    if (! $ok) {
        throw new RuntimeException($mensaje);
    }
}

function rechaza(callable $accion): void
{
    try {
        $accion();
    } catch (ValidationException|ModelNotFoundException $e) {
        return;
    }
    throw new RuntimeException('Se esperaba el rechazo de la operación.');
}

function datosPasajeros(array $asientos): array
{
    return array_map(fn ($asiento) => [
        'numero_asiento' => $asiento,
        'nombre' => 'Pasajero',
        'apellido' => 'Prueba',
        'documento_identidad' => 'DOC-'.$asiento,
        'fecha_nacimiento' => '1990-01-01',
        'tipo_pasajero' => 'adulto',
    ], $asientos);
}

function pasajeros(Reserva $reserva): array
{
    return datosPasajeros($reserva->pasajes->pluck('numero_asiento')->all());
}

function crearConPasajeros(User $cliente, int $tarifaId, array $asientos): Reserva
{
    $reserva = ReservaService::aplicarReserva($cliente, $tarifaId);

    return ReservaService::registrarPasajeros($cliente, $reserva->id, datosPasajeros($asientos));
}

try {
    Carbon::setTestNow(Carbon::parse('2026-10-01 08:00:00'));
    config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:', 'hashing.bcrypt.rounds' => 4]);
    DB::purge('sqlite');
    foreach (glob(dirname(__DIR__).'/database/migrations/*.php') as $file) {
        (require $file)->up();
    }
    DB::table('groups_admin')->insert(['id' => 1, 'name' => 'Administración', 'url' => 'administracion']);
    DB::table('empresas')->insert(['id' => 1, 'nombre' => 'Prueba', 'rif' => 'TEST', 'telefono' => '123', 'email' => 'test@example.test']);
    DB::table('estados')->insert(['id' => 1, 'nombre' => 'Prueba']);
    foreach ([1, 2, 3] as $id) {
        DB::table('terminales')->insert(['id' => $id, 'estado_id' => 1, 'nombre' => 'Terminal '.$id, 'direccion' => 'Prueba', 'latitud' => 0, 'longitud' => 0]);
    }
    DB::table('viajes')->insert(['id' => 1, 'empresa_id' => 1, 'origen_terminal_id' => 1, 'destino_terminal_id' => 3, 'duracion_estimada' => '02:00:00']);
    foreach ([1, 2] as $orden) {
        DB::table('viaje_tramos')->insert(['viaje_id' => 1, 'origen_terminal_id' => $orden, 'destino_terminal_id' => $orden + 1, 'orden' => $orden, 'duracion_estimada' => '01:00:00']);
    }
    DB::table('autobuses')->insert(['id' => 1, 'empresa_id' => 1, 'modelo' => 'Prueba', 'tipo_asiento' => 'Prueba', 'total_asientos' => 40]);
    DB::table('programaciones')->insert(['id' => 1, 'viaje_id' => 1, 'autobus_id' => 1, 'fecha_salida' => '2026-10-02', 'hora_salida' => '08:00:00', 'asientos_totales' => 40]);
    foreach ([[1, 1, 2, '15.00'], [2, 2, 3, '15.00'], [3, 1, 3, '25.00']] as [$id, $origen, $destino, $precio]) {
        ProgramacionTramoPrecio::create(['id' => $id, 'programacion_id' => 1, 'origen_terminal_id' => $origen, 'destino_terminal_id' => $destino, 'precio' => $precio]);
    }
    foreach ([1, 2] as $id) {
        DB::table('users')->insert(['id' => $id, 'name' => 'Cliente', 'lastname' => 'Prueba', 'email' => 'buyer'.$id.'@example.test']);
    }
    $cliente = User::findOrFail(1);
    $otro = User::findOrFail(2);
    $tasa = TasaServicio::create(['tipo_servicio' => 2, 'cantidad' => '10.00', 'monto_minimo' => '0.00', 'monto_maximo' => null, 'estatus' => 1]);

    $ab = ReservaService::aplicarReserva($cliente, 1);
    comprobar($ab->estado_pago === Reserva::ESTADO_PAGO_NUEVO && $ab->pasajes->isEmpty(), 'Reserva inicial sin boletos ni pasajeros');
    comprobar($ab->monto_pasajes === '15.00' && $ab->tasa_servicio === '1.50' && $ab->monto_total === '16.50', 'Cotización inicial de un pasaje');
    comprobar(in_array(12, ReservaService::consultarDisponibilidad(1)['asientos']), 'La reserva vacía no bloquea asientos');
    rechaza(fn () => ReservaService::prepararResumen($cliente, $ab->id));
    rechaza(fn () => ReservaService::pasarAPendiente($cliente, $ab->id, 'transferencia'));
    $ab = ReservaService::registrarPasajeros($cliente, $ab->id, datosPasajeros([12]));
    $bc = crearConPasajeros($otro, 2, [12]);
    $tramos = ReservaService::consultarDisponibilidadPorTramos(Programacion::whereKey(1)->get())[1];
    comprobar($tramos[1]['disponibles'] === 39 && $tramos[2]['disponibles'] === 39, 'Disponibilidad por tramo adyacente');
    comprobar($tramos[3]['ocupados'] === 1 && $tramos[3]['disponibles'] === 39, 'Un mismo asiento en tramos adyacentes no se resta dos veces');
    comprobar($ab->fecha_expiracion->equalTo(now()->addMinutes(20)), 'Plazo de bloqueo');
    rechaza(fn () => crearConPasajeros($otro, 3, [12]));
    rechaza(fn () => crearConPasajeros($otro, 1, [12]));
    rechaza(fn () => crearConPasajeros($cliente, 1, [1, 1]));
    rechaza(fn () => crearConPasajeros($cliente, 1, [41]));
    rechaza(fn () => ReservaService::registrarPasajeros($otro, $ab->id, pasajeros($ab)));

    ReservaService::registrarPasajeros($cliente, $ab->id, pasajeros($ab));
    $count = Viajero::count();
    ReservaService::registrarPasajeros($cliente, $ab->id, pasajeros($ab));
    comprobar(Viajero::count() === $count, 'Pasajeros duplicados en reintento');
    $ab = ReservaService::prepararResumen($cliente, $ab->id);
    comprobar($ab->monto_total === '16.50' && $ab->pasajes->first()->tasa_servicio === '1.50', 'Tasa porcentual');
    $tasa->update(['cantidad' => '99.00']);
    comprobar(ReservaService::prepararResumen($cliente, $ab->id)->monto_total === '16.50', 'Cambio indebido de tasa histórica');
    $tasa->update(['cantidad' => '10.00']);

    $campana = ConfiguracionCupon::create(['nombre_campana' => 'Prueba', 'tipo_cupon' => 'unico', 'modalidad' => 'codigo', 'codigo_base' => 'TEST', 'cantidad_generar' => 3, 'tipo_descuento' => 'fijo', 'monto_descuento' => '5.01', 'aplica_a' => 'pasajes', 'fecha_inicio' => now()->subDay(), 'fecha_fin' => now()->addDay(), 'estatus' => true]);
    $cupon = Cupon::create(['configuracion_cupon_id' => $campana->id, 'codigo' => 'TEST-1', 'redimido' => false]);
    $cuponDinamico = Cupon::create(['configuracion_cupon_id' => $campana->id, 'codigo' => 'TEST-DINAMICO', 'redimido' => false]);
    $dinamica = ReservaService::aplicarReserva($cliente, 3);
    $vencimiento = $dinamica->fecha_expiracion->toISOString();
    $dinamica = ReservaService::registrarPasajeros($cliente, $dinamica->id, datosPasajeros([21]));
    ReservaService::aplicarCupon($cliente, $dinamica->id, $cuponDinamico->codigo);
    $dinamica = ReservaService::registrarPasajeros($cliente, $dinamica->id, datosPasajeros([21, 22]));
    comprobar($dinamica->pasajes->count() === 2 && $dinamica->monto_total === '49.49', 'Añadir pasajero y repartir cupón');
    comprobar($dinamica->fecha_expiracion->toISOString() === $vencimiento, 'Añadir no renueva la reserva');
    $dinamica = ReservaService::registrarPasajeros($cliente, $dinamica->id, datosPasajeros([22]));
    comprobar($dinamica->pasajes->count() === 1 && $dinamica->monto_total === '21.99', 'Quitar pasajero y recalcular descuento y tasa');
    comprobar(in_array(21, ReservaService::consultarDisponibilidad(3)['asientos']), 'Quitar libera el asiento');
    $antes = $dinamica->pasajes->pluck('id')->all();
    rechaza(fn () => ReservaService::registrarPasajeros($cliente, $dinamica->id, datosPasajeros([12, 22])));
    comprobar($dinamica->pasajes()->pluck('id')->all() === $antes, 'Conflicto conserva la reserva anterior');
    $dinamica = ReservaService::registrarPasajeros($cliente, $dinamica->id, []);
    comprobar($dinamica->pasajes->isEmpty() && $dinamica->cupon_id === null && $dinamica->monto_total === '27.50', 'Sin pasajeros recupera la cotización unitaria');
    comprobar($dinamica->fecha_expiracion->toISOString() === $vencimiento, 'Quitar no renueva la reserva');
    rechaza(fn () => ReservaService::pasarAPendiente($cliente, $dinamica->id, 'transferencia'));
    ReservaService::cancelarReserva($cliente, $dinamica->id);
    $multiple = crearConPasajeros($cliente, 3, [1, 2]);
    ReservaService::registrarPasajeros($cliente, $multiple->id, pasajeros($multiple));
    ReservaService::aplicarCupon($cliente, $multiple->id, 'TEST-1');
    $multiple = ReservaService::prepararResumen($cliente, $multiple->id);
    comprobar($multiple->descuento_aplicado === '5.01' && $multiple->tasa_servicio === '4.50' && $multiple->monto_total === '49.49', 'Reparto de descuento y redondeo por boleto');
    rechaza(fn () => ReservaService::aplicarCupon($cliente, $ab->id, 'TEST-1'));
    ReservaService::aplicarCupon($cliente, $multiple->id, null);
    comprobar(ReservaService::prepararResumen($cliente, $multiple->id)->monto_total === '55.00', 'Quitar cupón');
    ReservaService::aplicarCupon($cliente, $multiple->id, 'TEST-1');
    $multiple = ReservaService::pasarAPendiente($cliente, $multiple->id, 'tarjeta');
    comprobar($multiple->fecha_expiracion === null && $multiple->estado_pago === Reserva::ESTADO_PAGO_PENDIENTE, 'Pendiente sin expiración');
    ReservaService::pasarAPendiente($cliente, $multiple->id, 'tarjeta');
    rechaza(fn () => ReservaService::pasarAPendiente($cliente, $multiple->id, 'transferencia'));
    rechaza(fn () => ReservaService::aplicarCupon($cliente, $multiple->id, null));
    rechaza(fn () => ReservaService::registrarPasajeros($cliente, $multiple->id, pasajeros($multiple)));
    rechaza(fn () => ReservaService::confirmarPago($multiple->id, '49.48'));
    $multiple = ReservaService::confirmarPago($multiple->id, '49.49');
    comprobar($multiple->estado_pago === Reserva::ESTADO_PAGO_PAGADO && $cupon->fresh()->redimido, 'Confirmación y redención');
    ReservaService::confirmarPago($multiple->id, '49.49');
    rechaza(fn () => ReservaService::marcarPagoFallido($multiple->id));

    ReservaService::pasarAPendiente($cliente, $ab->id, 'transferencia');
    $nuevaVencida = crearConPasajeros($cliente, 3, [13]);
    Carbon::setTestNow(now()->addMinutes(20));
    $tramos = ReservaService::consultarDisponibilidadPorTramos(Programacion::whereKey(1)->get())[1];
    comprobar($tramos[1]['ocupados'] === 3 && $tramos[2]['ocupados'] === 2, 'Solo pagadas y pendientes conservan el bloqueo al vencer las nuevas');
    ProgramacionTramoPrecio::findOrFail(2)->update(['asientos_maximos_permitidos' => 10]);
    $limitado = ReservaService::consultarDisponibilidadPorTramos(Programacion::whereKey(1)->get())[1][2];
    comprobar($limitado['capacidad'] === 10 && $limitado['ocupados'] === 2 && $limitado['disponibles'] === 8, 'Tope 10 menos 2 ocupados deja 8 disponibles');
    comprobar(ReservaService::consultarDisponibilidad(2)['cupo_tramo'] === 8, 'La compra usa la misma disponibilidad que el panel');
    ProgramacionTramoPrecio::findOrFail(2)->update(['asientos_maximos_permitidos' => null]);
    rechaza(fn () => ReservaService::pasarAPendiente($cliente, $nuevaVencida->id, 'transferencia'));
    rechaza(fn () => crearConPasajeros($otro, 1, [12])); // Pendiente conserva su asiento.
    ReservaService::pasarAPendiente($cliente, $ab->id, 'transferencia'); // Reintento después del plazo original.
    ReservaService::confirmarPago($ab->id, '16.50'); // La revisión administrativa puede terminar después.
    $reutilizada = crearConPasajeros($cliente, 3, [13]);
    rechaza(fn () => crearConPasajeros($otro, 1, [1])); // El boleto pagado no vence.
    ReservaService::cancelarReserva($cliente, $reutilizada->id);
    $otra = crearConPasajeros($otro, 3, [13]);
    ReservaService::registrarPasajeros($otro, $otra->id, pasajeros($otra));
    ReservaService::pasarAPendiente($otro, $otra->id, 'tarjeta');
    ReservaService::marcarPagoFallido($otra->id);
    crearConPasajeros($cliente, 3, [13]);

    ProgramacionTramoPrecio::findOrFail(1)->update(['asientos_maximos_permitidos' => 5]);
    crearConPasajeros($cliente, 1, [10]);
    rechaza(fn () => crearConPasajeros($otro, 1, [11]));
    $tramos = ReservaService::consultarDisponibilidadPorTramos(Programacion::whereKey(1)->get())[1];
    comprobar($tramos[1]['cupo_tramo'] === 0 && $tramos[1]['disponibles'] === 0, 'El tope descuenta todos los asientos que atraviesan el tramo');
    ProgramacionTramoPrecio::findOrFail(1)->update(['asientos_maximos_permitidos' => null]);
    $tasa->update(['tipo_servicio' => 1, 'cantidad' => '0.00']);
    $cero = crearConPasajeros($cliente, 1, [11]);
    ReservaService::registrarPasajeros($cliente, $cero->id, pasajeros($cero));
    comprobar(ReservaService::prepararResumen($cliente, $cero->id)->tasa_servicio === '0.00', 'Tasa cero');

    // Si no hay una tasa aplicable, el intento no cambia a pendiente ni deja cálculos parciales.
    $sinTasa = ReservaService::aplicarReserva($cliente, 1);
    $tasa->update(['estatus' => false]);
    rechaza(fn () => ReservaService::registrarPasajeros($cliente, $sinTasa->id, datosPasajeros([14])));
    rechaza(fn () => ReservaService::pasarAPendiente($cliente, $sinTasa->id, 'tarjeta'));
    comprobar($sinTasa->fresh()->estado_pago === Reserva::ESTADO_PAGO_NUEVO && ! $sinTasa->pasajes()->exists(), 'Rollback al registrar sin tasa');

    // Cupón porcentual, propiedad y vigencia comercial.
    $tasa->update(['estatus' => true, 'tipo_servicio' => 2, 'cantidad' => '10.00']);
    $sinTasa = ReservaService::registrarPasajeros($cliente, $sinTasa->id, datosPasajeros([14]));
    $campana->update(['tipo_descuento' => 'porcentaje', 'monto_descuento' => '100.00']);
    $cuponCero = Cupon::create(['configuracion_cupon_id' => $campana->id, 'codigo' => 'TEST-CERO', 'usuario_id' => $cliente->id]);
    ReservaService::aplicarCupon($cliente, $sinTasa->id, 'TEST-CERO');
    $gratis = ReservaService::prepararResumen($cliente, $sinTasa->id);
    comprobar($gratis->monto_total === '0.00' && $gratis->descuento_aplicado === '15.00', 'Cupón porcentual completo');
    ReservaService::aplicarCupon($cliente, $sinTasa->id, null);
    $ajena = crearConPasajeros($otro, 1, [20]);
    ReservaService::registrarPasajeros($otro, $ajena->id, pasajeros($ajena));
    rechaza(fn () => ReservaService::aplicarCupon($otro, $ajena->id, 'TEST-CERO'));
    $campana->update(['fecha_fin' => now()->subSecond()]);
    rechaza(fn () => ReservaService::aplicarCupon($cliente, $sinTasa->id, 'TEST-CERO'));

    // Tramos invertidos, salida ya realizada y embarque intermedio todavía futuro.
    $inversa = ProgramacionTramoPrecio::create(['programacion_id' => 1, 'origen_terminal_id' => 3, 'destino_terminal_id' => 1, 'precio' => '10.00']);
    rechaza(fn () => crearConPasajeros($cliente, $inversa->id, [30]));
    Carbon::setTestNow(Carbon::parse('2026-10-02 08:30:00'));
    rechaza(fn () => crearConPasajeros($cliente, 1, [30]));
    $intermedia = crearConPasajeros($cliente, 2, [30]);
    comprobar($intermedia->origen_terminal_id == 2, 'Embarque intermedio');

    echo "OK: checkout completo, tramos, expiración, pasajeros, propiedad, cupones, tasas, pagos, cupos y reintentos.\n";
} catch (Throwable $e) {
    fwrite(STDERR, get_class($e).': '.$e->getMessage().' en '.$e->getFile().':'.$e->getLine()."\n");
    exit(1);
} finally {
    Carbon::setTestNow();
}
