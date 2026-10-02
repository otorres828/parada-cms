<?php

// Reutiliza las fixtures aisladas de SQLite; nunca toca la base local.
require __DIR__.'/ListadosEmpresaSmoke.php';

use App\Models\Reserva;
use App\Models\Pasaje;
use App\Models\Programacion;
use App\Models\ProgramacionTramoPrecio;
use App\Models\UsuarioEmpresa;
use App\Models\DatoBancario;
use App\Services\Empresa\ReservaTaquillaService;
use App\Services\Empresa\PagoTaquillaService;
use App\Services\TasasServicioService;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

$salidaTaquilla = $programacion->replicate();
$salidaTaquilla->asientos_totales = 2;
$salidaTaquilla->save();
$tarifaTaquilla = $tarifa->replicate();
$tarifaTaquilla->programacion_id = $salidaTaquilla->id;
$tarifaTaquilla->save();
$cuenta = DatoBancario::where('empresa_id', $empresa->id)->firstOrFail();
$comprador = ['nombre' => 'Compra presencial', 'telefono' => '04141234567', 'email' => null];
$persona = ['nombre' => 'Menor', 'apellido' => 'Perez', 'tipo_documento' => null, 'documento_identidad' => null, 'fecha_nacimiento' => '2020-01-01', 'tipo_pasajero' => 'nino'];
$usuariosAntes = App\Models\User::count();
$viajerosAntes = App\Models\Viajero::count();
$venta = ReservaTaquillaService::crear($usuarioEmpresa, $tarifaTaquilla->id);
$check($venta->usuario_id === null && $venta->usuario_empresa_id === $usuarioEmpresa->id);
$check($venta->receptor_pago === 'empresa' && $venta->tasa_servicio === '0.00');
$check($venta->pasajes->isEmpty() && $venta->monto_total === '10.00');
$check($venta->fecha_expiracion->isFuture());
ReservaTaquillaService::guardarComprador($usuarioEmpresa, $venta->id, $comprador);
$check(! str_contains(DB::table('reservas')->where('id', $venta->id)->value('comprador_json'), 'Compra presencial'));
$venta = ReservaTaquillaService::agregarPasajero($usuarioEmpresa, $venta->id, $persona);
$primerPasaje = $venta->pasajes->first();
$check($primerPasaje->viajero_id === null && $primerPasaje->getQr() === null);
$venta = ReservaTaquillaService::agregarPasajero($usuarioEmpresa, $venta->id, array_merge($persona, ['nombre' => 'Otro']));
$check($venta->monto_total === '20.00' && $venta->tasa_servicio === '0.00');
$check($venta->pasajes->pluck('numero_asiento')->unique()->count() === 2);
$reject(function () use ($usuarioEmpresa, $venta, $persona) {
    ReservaTaquillaService::agregarPasajero($usuarioEmpresa, $venta->id, $persona);
}, ValidationException::class);
$check(Pasaje::where('reserva_id', $venta->id)->count() === 2);
$venta = ReservaTaquillaService::removerPasajero($usuarioEmpresa, $venta->id, $primerPasaje->id);
$check($venta->monto_total === '10.00');
$venta = TasasServicioService::calcularTasasReserva($venta);
$check($venta->tasa_servicio === '0.00');
$venta = PagoTaquillaService::registrar($usuarioEmpresa, $venta->id, \App\Models\PagoReserva::TIPO_PAGO_TRANSFERENCIA, $cuenta->id, 'TRANSFERENCIA-TAQUILLA');
$check($venta->estado_pago === Reserva::ESTADO_PAGO_PENDIENTE && $venta->fecha_expiracion === null);
$check($venta->pago->tipo_pago === \App\Models\PagoReserva::TIPO_PAGO_TRANSFERENCIA);
$venta = PagoTaquillaService::confirmar($usuarioEmpresa, $venta->id);
$check($venta->estado_pago === Reserva::ESTADO_PAGO_PAGADO);
$check($venta->pasajes->first()->getQr() !== null);
$reject(function () use ($usuarioEmpresa, $venta) {
    PagoTaquillaService::registrar($usuarioEmpresa, $venta->id, \App\Models\PagoReserva::TIPO_PAGO_EFECTIVO, null, null);
}, ValidationException::class);

$efectivo = ReservaTaquillaService::crear($usuarioEmpresa, $tarifaTaquilla->id);
ReservaTaquillaService::guardarComprador($usuarioEmpresa, $efectivo->id, $comprador);
ReservaTaquillaService::agregarPasajero($usuarioEmpresa, $efectivo->id, $persona);
$efectivo = PagoTaquillaService::registrar($usuarioEmpresa, $efectivo->id, \App\Models\PagoReserva::TIPO_PAGO_EFECTIVO, null, null);
$check($efectivo->estado_pago === Reserva::ESTADO_PAGO_PAGADO && $efectivo->pago->metodo_pago === null);
$check($efectivo->pago->tipo_pago === \App\Models\PagoReserva::TIPO_PAGO_EFECTIVO && $efectivo->pago->tasa_servicio === '0.00');
$check(App\Models\User::count() === $usuariosAntes && App\Models\Viajero::count() === $viajerosAntes);

$ajeno = $usuarioEmpresa->replicate();
$ajeno->empresa_id = $empresaDos->id;
$ajeno->email = 'ajeno-taquilla@test.test';
$ajeno->save();
$reject(function () use ($ajeno, $venta) {
    PagoTaquillaService::confirmar($ajeno, $venta->id);
}, ModelNotFoundException::class);
$reject(function () use ($ajeno, $tarifaTaquilla) {
    ReservaTaquillaService::crear($ajeno, $tarifaTaquilla->id);
}, ModelNotFoundException::class);
$ajeno->es_admin = 0;
$reject(function () use ($ajeno, $venta) {
    PagoTaquillaService::confirmar($ajeno, $venta->id);
}, Symfony\Component\HttpKernel\Exception\HttpException::class);
echo "Taquilla: comprador cifrado, sin cuentas ficticias, cupos, tasa cero, cobros y aislamiento OK\n";

// Renderiza el componente Livewire real para detectar variables o componentes Blade inválidos.
$html = Livewire\Livewire::mount(App\Livewire\Empresas\Reservas\SaveReserva::class, ['reserva_id' => $efectivo->id]);
$check(str_contains($html, $efectivo->codigo_referencia));
$check(str_contains($html, 'data:image/svg+xml;base64,'));
$htmlNuevo = Livewire\Livewire::mount(App\Livewire\Empresas\Reservas\SaveReserva::class);
$check(str_contains($htmlNuevo, 'Seleccionar salida'));

// Expiración y recuperación de puestos al cancelar.
$otraSalida = $programacion->replicate();
$otraSalida->save();
$otraTarifa = $tarifa->replicate();
$otraTarifa->programacion_id = $otraSalida->id;
$otraTarifa->save();
$vencida = ReservaTaquillaService::crear($usuarioEmpresa, $otraTarifa->id);
$vencida->update(['fecha_expiracion' => now()->subMinute()]);
$reject(function () use ($usuarioEmpresa, $vencida, $persona) {
    ReservaTaquillaService::agregarPasajero($usuarioEmpresa, $vencida->id, $persona);
}, ValidationException::class);
$cancelada = ReservaTaquillaService::cancelar($usuarioEmpresa, $vencida->id);
$check($cancelada->estado_pago === Reserva::ESTADO_PAGO_CANCELADO);
$sinPasajeros = ReservaTaquillaService::crear($usuarioEmpresa, $otraTarifa->id);
ReservaTaquillaService::guardarComprador($usuarioEmpresa, $sinPasajeros->id, $comprador);
$reject(function () use ($usuarioEmpresa, $sinPasajeros) {
    PagoTaquillaService::registrar($usuarioEmpresa, $sinPasajeros->id, \App\Models\PagoReserva::TIPO_PAGO_EFECTIVO, null, null);
}, ValidationException::class);
$check(!$sinPasajeros->pago()->exists());

// Envío bajo acción explícita; se usa un transporte falso y no salen correos reales.
Illuminate\Support\Facades\Mail::fake();
$efectivo->update(['comprador_json' => array_merge($comprador, ['email' => 'comprador@example.test'])]);
$componente = new App\Livewire\Empresas\Reservas\SaveReserva;
$componente->boot();
$componente->mount($efectivo->id);
$componente->enviarPasajes();
$check(Illuminate\Support\Facades\Mail::sent(App\Mail\PasajesTaquilla::class)->count() === 1);
$correo = new App\Mail\PasajesTaquilla($efectivo->fresh()->detalle());
$check(count($correo->attachments()) === 1);
$check(str_contains($correo->render(), $efectivo->codigo_referencia));
echo "Taquilla: vistas Livewire, vencimiento, cancelación y correo simulado OK\n";

// Un cajero con permiso de venta puede reportar transferencia, pero no aprobar cobros.
(new Database\Seeders\GroupSectionPermissionEmpresaSeeder)->run();
$cajero = $usuarioEmpresa->replicate();
$cajero->email = 'cajero@test.test';
$cajero->es_admin = 0;
$cajero->save();
$permisoVenta = App\Models\PermissionEmpresa::where('url', 'add')->whereHas('section', function ($query) {
    $query->where('url', 'reservas');
})->firstOrFail();
$cajero->permisos()->attach($permisoVenta->id);
$ventaCajero = ReservaTaquillaService::crear($cajero, $otraTarifa->id);
ReservaTaquillaService::guardarComprador($cajero, $ventaCajero->id, $comprador);
ReservaTaquillaService::agregarPasajero($cajero, $ventaCajero->id, $persona);
$reject(function () use ($cajero, $ventaCajero) {
    PagoTaquillaService::registrar($cajero, $ventaCajero->id, \App\Models\PagoReserva::TIPO_PAGO_EFECTIVO, null, null);
}, Symfony\Component\HttpKernel\Exception\HttpException::class);
$check(!$ventaCajero->pago()->exists());
$ventaCajero = PagoTaquillaService::registrar($cajero, $ventaCajero->id, \App\Models\PagoReserva::TIPO_PAGO_TRANSFERENCIA, $cuenta->id, 'CAJERO-TRANSFERENCIA');
$reject(function () use ($cajero, $ventaCajero) {
    PagoTaquillaService::confirmar($cajero, $ventaCajero->id);
}, Symfony\Component\HttpKernel\Exception\HttpException::class);
$ventaCajero = PagoTaquillaService::rechazar($usuarioEmpresa, $ventaCajero->id);
$check($ventaCajero->estado_pago === Reserva::ESTADO_PAGO_FALLIDO);
$check(!Reserva::reservasQueBloqueanAsientos()->whereKey($ventaCajero->id)->exists());
echo "Taquilla: permisos de vendedor y confirmador separados, rechazo libera puestos OK\n";

// Cotizar no debe escribir registros ni retener cupos.
$draft = new App\Livewire\Empresas\Reservas\SaveReserva;
$draft->boot();
$draft->mount();
$antes = [Reserva::count(), Pasaje::count(), App\Models\PagoReserva::count()];
$draft->pasajero = $persona;
$draft->agregarPasajero();
$check(count($draft->pasajeros) === 1);
$draft->removerPasajero(0);
$check($draft->pasajeros === []);
$check($antes === [Reserva::count(), Pasaje::count(), App\Models\PagoReserva::count()]);

$salidaMixta = $programacion->replicate();
$salidaMixta->asientos_totales = 2;
$salidaMixta->save();
$tarifaMixta = $tarifa->replicate();
$tarifaMixta->programacion_id = $salidaMixta->id;
$tarifaMixta->save();
$infante = array_merge($persona, ['nombre' => 'Bebé', 'tipo_pasajero' => 'infante', 'con_asiento' => false]);
$pagosMixtos = [
    ['tipo' => 2, 'moneda' => 'USD', 'monto' => '4.00'],
    ['tipo' => 2, 'moneda' => 'VES', 'monto' => number_format(6 * App\Models\TipoCambio::vigente()->valor_usd, 2, '.', '')],
];
$antes = [Reserva::count(), Pasaje::count(), App\Models\PagoReserva::count()];
$reject(function () use ($usuarioEmpresa, $tarifaMixta, $comprador, $persona, $infante) {
    ReservaTaquillaService::registrar($usuarioEmpresa, $tarifaMixta->id, $comprador, [$persona, $infante], [['tipo' => 2, 'moneda' => 'USD', 'monto' => '1.00']]);
}, ValidationException::class);
$check($antes === [Reserva::count(), Pasaje::count(), App\Models\PagoReserva::count()]);
$mixta = ReservaTaquillaService::registrar($usuarioEmpresa, $tarifaMixta->id, $comprador, [$persona, $infante], $pagosMixtos);
$check($mixta->monto_total === '10.00' && $mixta->estado_pago === Reserva::ESTADO_PAGO_PAGADO);
$check($mixta->pagos()->count() === 2 && $mixta->pasajes->count() === 2);
$sinAsiento = $mixta->pasajes->firstWhere('numero_asiento', null);
$check($sinAsiento !== null && $sinAsiento->total === '0.00' && $sinAsiento->tasa_servicio === '0.00');
$check(Pasaje::consultarDisponibilidad($tarifaMixta->id)['cupo_tramo'] === 1);
$infante['con_asiento'] = true;
$conAsiento = ReservaTaquillaService::registrar($usuarioEmpresa, $tarifaMixta->id, $comprador, [$infante], [['tipo' => 2, 'moneda' => 'USD', 'monto' => '10.00']]);
$check($conAsiento->pasajes->first()->numero_asiento !== null && $conAsiento->monto_total === '10.00');
$antes = Reserva::count();
$reject(function () use ($usuarioEmpresa, $tarifaMixta, $comprador, $persona) {
    ReservaTaquillaService::registrar($usuarioEmpresa, $tarifaMixta->id, $comprador, [$persona], [['tipo' => 2, 'moneda' => 'USD', 'monto' => '10.00']]);
}, ValidationException::class);
$check(Reserva::count() === $antes);
$check(str_contains(Livewire\Livewire::mount(App\Livewire\Empresas\Reservas\SaveReserva::class, ['reserva_id' => $mixta->id]), 'Pagos registrados'));
$check(str_contains(Livewire\Livewire::mount(App\Livewire\Empresas\DatosBancarios\ListDatoBancario::class), 'Datos Bancarios'));
$check(str_contains(Livewire\Livewire::mount(App\Livewire\Empresas\DatosBancarios\SaveDatoBancario::class), 'Seleccionar banco'));
echo "Cotización sin escrituras, pagos combinados, rollback, infantes y cuentas bancarias: OK\n";

$salidaMovil = $programacion->replicate();
$salidaMovil->save();
$tarifaMovil = $tarifa->replicate();
$tarifaMovil->programacion_id = $salidaMovil->id;
$tarifaMovil->save();
$cuentaMovil = $cuenta->replicate();
$cuentaMovil->tipo = DatoBancario::PAGO_MOVIL;
$cuentaMovil->save();
$cuentaTarjeta = $cuenta->replicate();
$cuentaTarjeta->tipo = DatoBancario::CUENTA_BANCARIA;
$cuentaTarjeta->save();
$cobros = [
    ['tipo' => 3, 'moneda' => 'VES', 'monto' => number_format(4 * App\Models\TipoCambio::vigente()->valor_usd, 2, '.', ''), 'cuenta_id' => $cuentaMovil->id, 'referencia' => 'MOVIL-MIXTO'],
    ['tipo' => 4, 'moneda' => 'USD', 'monto' => '6.00', 'cuenta_id' => $cuentaTarjeta->id, 'referencia' => 'TARJETA-MIXTO'],
];
$movil = ReservaTaquillaService::registrar($usuarioEmpresa, $tarifaMovil->id, $comprador, [$persona], $cobros, 'TQ-PRUEBA-UNICA');
$check($movil->estado_pago === Reserva::ESTADO_PAGO_PENDIENTE && $movil->pagos()->count() === 2);
$repetida = ReservaTaquillaService::registrar($usuarioEmpresa, $tarifaMovil->id, $comprador, [$persona], $cobros, 'TQ-PRUEBA-UNICA');
$check($movil->id === $repetida->id);
$movil = PagoTaquillaService::confirmar($usuarioEmpresa, $movil->id);
$check($movil->estado_pago === Reserva::ESTADO_PAGO_PAGADO);
$antes = Reserva::count();
$cuentaAjena = $cuenta->replicate();
$cuentaAjena->empresa_id = $empresaDos->id;
$cuentaAjena->save();
$cobros[0]['cuenta_id'] = $cuentaAjena->id;
$cobros[0]['referencia'] = 'OTRA-EMPRESA';
$reject(function () use ($usuarioEmpresa, $tarifaMovil, $comprador, $persona, $cobros) {
    ReservaTaquillaService::registrar($usuarioEmpresa, $tarifaMovil->id, $comprador, [$persona], $cobros);
}, ModelNotFoundException::class);
$check(Reserva::count() === $antes);
$bank = new App\Livewire\Empresas\DatosBancarios\SaveDatoBancario;
$bank->boot();
$bank->mount();
$bank->datos = [
    'tipo' => 1,
    'banco' => 'Banesco',
    'nombre_titular' => 'Titular de prueba',
    'tipo_titular' => 'personal',
    'numero_documento' => '12345678',
    'numero_cuenta_telefono' => '04141234567',
    'tipo_cuenta' => null,
    'estatus' => 1,
];
$bank->save();
$check(DatoBancario::findOrFail($bank->cuentaId)->empresa_id === $usuarioEmpresa->empresa_id);
$reject(function () use ($bank, $empresaDos) {
    $bank->mount(DatoBancario::where('empresa_id', $empresaDos->id)->firstOrFail()->id);
}, ModelNotFoundException::class);
echo "Pago móvil y tarjeta, suma confirmada, reintentos y bancos aislados: OK\n";
