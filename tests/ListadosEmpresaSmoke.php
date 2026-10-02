<?php

use App\Models\Admin;
use App\Models\Transporte;
use App\Models\ConfiguracionCupon;
use App\Models\DatoBancario;
use App\Models\Empresa;
use App\Models\Estado;
use App\Models\Programacion;
use App\Models\OrdenCobro;
use App\Models\Pasaje;
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
use App\Services\ViajeroService;
use App\Support\ConversorMoneda;
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
$cliente = User::create(['name' => 'Ana', 'lastname' => 'Perez', 'email' => 'ana@test.test', 'telefono' => '0414-1234567']);
$otro = User::create(['name' => 'Otro', 'lastname' => 'Perez', 'email' => 'otro@test.test']);
$check(DB::table('users')->where('id', $cliente->id)->value('telefono') !== '0414-1234567');
$check(User::searchAdmin('04141234567')->whereKey($cliente->id)->exists());
$empresa = Empresa::create(['nombre' => 'Empresa', 'rif' => 'J1', 'telefono' => '123', 'email' => 'empresa@test.test', 'estatus' => 1]);
$estado = Estado::create(['nombre' => 'Estado']);
$terminales = [];
foreach (['Origen', 'Destino'] as $nombre) {
    $terminales[] = Terminal::create(['estado_id' => $estado->id, 'nombre' => $nombre, 'direccion' => 'Calle', 'latitud' => 0, 'longitud' => 0]);
}
$bus = Transporte::create(['empresa_id' => $empresa->id, 'modelo' => 'Bus', 'tipo_asiento' => 'Normal', 'total_asientos' => 2, 'estatus' => 1, 'es_plantilla' => false]);
$viaje = Viaje::create(['empresa_id' => $empresa->id, 'origen_terminal_id' => $terminales[0]->id, 'destino_terminal_id' => $terminales[1]->id, 'duracion_estimada' => '01:00:00', 'estatus' => 1]);
$programacion = Programacion::create(['viaje_id' => $viaje->id, 'transporte_id' => $bus->id, 'fecha_salida' => today()->addDay(), 'hora_salida' => '18:00:00', 'asientos_totales' => 2, 'estatus' => 1]);
$tarifa = ProgramacionTramoPrecio::create(['programacion_id' => $programacion->id, 'origen_terminal_id' => $terminales[0]->id, 'destino_terminal_id' => $terminales[1]->id, 'precio' => '10.00']);
TasaServicio::create(['monto_minimo' => 0, 'monto_maximo' => null, 'cantidad' => 1, 'tipo_servicio' => 1, 'estatus' => 1]);
$tipoCambio = TipoCambio::create(['valor_usd' => '500.00000000', 'valor_eur' => '590.00000000', 'valor' => 1]);
$banco = DatoBancario::create(['empresa_id' => $empresa->id, 'tipo' => 1, 'banco' => 'Banco', 'nombre_titular' => 'Empresa', 'tipo_titular' => 'juridico', 'numero_documento' => 'J1', 'numero_cuenta_telefono' => '123', 'estatus' => 1]);
$pasajero = ['nombre' => 'Ana', 'apellido' => 'Perez', 'tipo_documento' => 1, 'documento_identidad' => 'V-12345678', 'fecha_nacimiento' => '1990-01-01', 'tipo_pasajero' => 'adulto'];
$viajeroCliente = ViajeroService::agregarViajero($cliente, $pasajero);
$viajeroClienteDos = ViajeroService::agregarViajero($cliente, array_merge($pasajero, [
    'nombre' => 'Beatriz',
    'documento_identidad' => 'V-12345679',
]));
$viajeroOtro = ViajeroService::agregarViajero($otro, $pasajero);
$viajeroOtroDos = ViajeroService::agregarViajero($otro, array_merge($pasajero, [
    'nombre' => 'Carlos',
    'documento_identidad' => 'V-12345680',
]));
// El servicio funciona sin sesión: el middleware y el consumidor seleccionan al cliente.
$r = ReservaService::aplicarReserva($cliente, $tarifa->id);
$check($r->pasajes->isEmpty() && $r->monto_total === '11.00' && $r->tipos_cambios_id === $tipoCambio->id && $r->monto_total_bolivares === '5500.00');
$reject(fn () => ReservaService::agregarPasajero($otro, $r->id, $viajeroCliente->id), ModelNotFoundException::class);
$r = ReservaService::agregarPasajero($cliente, $r->id, $viajeroCliente->id);

// Dos empresas con reservas equivalentes: las consultas deben aislarlas.
$empresaDos = Empresa::create(['nombre' => 'Segunda', 'telefono' => '456', 'email' => 'segunda@test.test', 'rif' => 'J2', 'estatus' => 1]);
$viajeDos = $viaje->replicate();
$viajeDos->empresa_id = $empresaDos->id;
$viajeDos->save();
$programacionDos = $programacion->replicate();
$programacionDos->viaje_id = $viajeDos->id;
$programacionDos->save();
$reservaDos = $r->replicate();
$reservaDos->programacion_id = $programacionDos->id;
$reservaDos->codigo_referencia = 'OTRA-EMPRESA';
$reservaDos->save();
$pasajeDos = $r->pasajes->first()->replicate();
$pasajeDos->reserva_id = $reservaDos->id;
$pasajeDos->localizador = 'otro-token';
$pasajeDos->save();
Reserva::whereIn('id', [$r->id, $reservaDos->id])->update(['estado_pago' => Reserva::ESTADO_PAGO_PAGADO]);
$usuarioEmpresa = new App\Models\UsuarioEmpresa;
$usuarioEmpresa->id = 1;
$usuarioEmpresa->empresa_id = $empresa->id;
$usuarioEmpresa->estatus = 1;
$usuarioEmpresa->es_admin = 1;
$usuarioEmpresa->nombre = 'Usuario';
$usuarioEmpresa->email = 'usuario@empresa.test';
$usuarioEmpresa->password = 'test';
$usuarioEmpresa->save();
auth('empresa')->setUser($usuarioEmpresa);
foreach ([
    'Reservas\\ListReserva' => ['reservas', $r->id],
    'Pasajes\\ListPasaje' => ['pasajes', $r->pasajes->first()->id],
    'Viajes\\ListViaje' => ['viajes', $viaje->id],
    'Programaciones\\ListProgramacion' => ['programaciones', $programacion->id],
    'Transportes\\ListTransporte' => ['transportes', $bus->id],
] as $nombre => [$variable, $id]) {
    $clase = 'App\\Livewire\\Empresas\\'.$nombre;
    $componente = new $clase;
    $componente->sortColumn = 'id';
    $componente->sortDirection = 'desc';
    if (property_exists($componente, 'date_from')) {
        $componente->date_from = today()->subDay()->format('Y-m-d');
        $componente->date_to = today()->addDays(2)->format('Y-m-d');
    }
    $filas = $componente->render()->getData()[$variable];
    $check($filas->pluck('id')->all() === [$id]);
    echo $nombre." aislado correctamente\n";
}
foreach (['SalesReport', 'RoutesReport'] as $nombre) {
    $clase = 'App\\Livewire\\Empresas\\Reportes\\'.$nombre;
    $componente = new $clase;
    $componente->date_from = today()->subDay()->format('Y-m-d');
    $componente->date_to = today()->addDay()->format('Y-m-d');
    $filas = $componente->render()->getData()['rows'];
    $check((int) $filas->sum('cantidad') === 1);
    echo $nombre." aislado correctamente\n";
}
echo "Listados de Empresas: OK\n";



$campana = ConfiguracionCupon::create([
    'nombre_campana' => 'Propia', 'empresa_id' => $empresa->id,
    'tipo_cupon' => ConfiguracionCupon::TIPO_PERSONALIZADO,
    'codigo_personalizado' => 'TEST', 'modalidad' => ConfiguracionCupon::MODALIDAD_GENERAL,
    'aplica_en' => 'pasajes', 'cantidad_generar' => 10,
    'tipo_descuento' => 'monto_fijo', 'monto_descuento' => 1,
    'fecha_inicio' => now()->subDay(), 'fecha_fin' => now()->addDay(), 'estatus' => 1,
]);
$campanaDos = $campana->replicate();
$campanaDos->empresa_id = $empresaDos->id;
$campanaDos->codigo_personalizado = 'OTRA';
$campanaDos->save();
$listado = new App\Livewire\Empresas\Cupones\ListCampana;
$listado->mount();
$check($listado->render()->getData()['cupones']->pluck('id')->all() === [$campana->id]);
$reject(function () use ($listado, $campanaDos) {
    $listado->changeStatus($campanaDos->id);
}, ModelNotFoundException::class);
$listado->changeStatus($campana->id);
$check($campana->fresh()->estatus == 2);
foreach (['Reservas\\ListReserva', 'Pasajes\\ListPasaje'] as $nombre) {
    $clase = 'App\\Livewire\\Empresas\\'.$nombre;
    $componente = new $clase;
    $componente->mount();
    $respuesta = $componente->exportExcel();
    $check($respuesta instanceof Symfony\Component\HttpFoundation\BinaryFileResponse);
    $libro = PhpOffice\PhpSpreadsheet\IOFactory::load($respuesta->getFile()->getPathname());
    $check($libro->getActiveSheet()->getHighestDataRow() === 2);
    $check(! str_contains(json_encode($libro->getActiveSheet()->toArray()), 'OTRA-EMPRESA'));
    $libro->disconnectWorksheets();
}
echo "Cupones aislados y exportaciones XLSX: OK\n";

// El formato empresarial cambia por contrato sin alterar el export de Admin.
$pasaje = $r->pasajes->first()->fresh();
$adminExport = new App\Exports\Admin\PasajesExport(Pasaje::query());
foreach ([true, false] as $mostrarTasa) {
    $export = new App\Exports\Empresas\PasajesExport(Pasaje::query(), $mostrarTasa);
    $columnas = $export->headings();
    $valores = $export->map($pasaje);
    $check(count($columnas) === count($valores));
    $check(count($columnas) === ($mostrarTasa ? 23 : 19));
    $check(in_array('Tasa de servicio USD', $columnas) === $mostrarTasa);
    $check(in_array('Subtotal USD', $columnas) === $mostrarTasa);
    $check(count(array_keys($columnas, 'Total USD', true)) === 1);
    $check($valores[array_search('Total USD', $columnas)] === (float) ($mostrarTasa ? $pasaje->total : $pasaje->subtotal));
}
$check(in_array('Subtotal USD', $adminExport->headings()));
$check(in_array('Tasa de servicio USD', $adminExport->headings()));
echo "Columnas y montos por contrato; Admin sin cambios: OK\n";

$dashboard = new App\Livewire\Empresas\Dashboard;
$dashboard->mount();
$datos = $dashboard->render()->getData();
$check($datos['metrics']['reservas_pagadas'] === 1);
$check($datos['metrics']['pasajes'] === 1);
$check($datos['totalReservas'] === 1);
$check($datos['ultimasReservas']->pluck('id')->all() === [$r->id]);
$check($datos['proximasSalidas']->pluck('id')->all() === [$programacion->id]);
$dashboard->date_from = '2500-01-01';
$dashboard->render();
$dashboard->periodo = '7';
$dashboard->updatedPeriodo();
$check($dashboard->date_from === today()->subDays(6)->toDateString());
echo "Dashboard empresarial aislado, períodos y fechas: OK\n";
