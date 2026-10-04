<?php

require __DIR__.'/ListadosEmpresaSmoke.php';

use App\Livewire\Empresas\Programaciones\SaveProgramacion;
use App\Livewire\Empresas\Programaciones\ListProgramacion;
use App\Livewire\Empresas\Viajes\ListViaje;
use App\Models\ViajeTramo;
use App\Models\Terminal;
use App\Models\Viaje;
use App\Models\Programacion;
use App\Services\Empresa\ProgramacionService;
use Livewire\Livewire;

$terminalIntermedio = Terminal::create([
    'estado_id' => $estado->id,
    'nombre' => 'Intermedio',
    'direccion' => 'Calle',
    'latitud' => 0,
    'longitud' => 0,
]);
$rutaPlantilla = Viaje::create([
    'empresa_id' => $empresa->id,
    'origen_terminal_id' => $terminales[0]->id,
    'destino_terminal_id' => $terminales[1]->id,
    'duracion_estimada' => '02:00:00',
    'estatus' => 1,
]);
foreach ([[$terminales[0]->id, $terminalIntermedio->id, '01:00:00', 7], [$terminales[0]->id, $terminales[1]->id, '02:00:00', 13], [$terminalIntermedio->id, $terminales[1]->id, '01:00:00', 8]] as $orden => $fila) {
    ViajeTramo::create([
        'viaje_id' => $rutaPlantilla->id,
        'origen_terminal_id' => $fila[0],
        'destino_terminal_id' => $fila[1],
        'duracion_estimada' => $fila[2],
        'precio' => $fila[3],
        'orden' => $orden + 1,
    ]);
}
$form = new SaveProgramacion;
$form->boot();
$form->mount();
$form->viajeId = (string) $rutaPlantilla->id;
$form->fechaSalida = today()->addDay()->format('Y-m-d');
$form->horaSalida = '23:30';
$form->updatedViajeId();
$check(count($form->tramos) === 3);
$clave = $terminalIntermedio->id.'-'.$terminales[1]->id;
$check($form->tramos[$clave]['hora_salida'] === '00:30');
$check($form->tramos[$clave]['fecha_salida'] === today()->addDays(2)->format('Y-m-d'));
foreach ($form->tramos as $key => &$fila) {
    $fila['habilitado'] = $key === $clave;
}
unset($fila);
$datos = [
    'viaje_id' => $rutaPlantilla->id,
    'transporte_id' => $bus->id,
    'estatus' => 1,
    'tramos' => $form->tramos,
];
$nueva = ProgramacionService::guardar($empresa->id, $datos);
$check($nueva->tramoPrecios->count() === 1 && $nueva->asientos_totales === $bus->total_asientos);
$check($nueva->getSalida()->format('H:i') === '00:30');
$check(Programacion::searchAdmin('', ['empresa_id' => $empresa->id])->find($nueva->id)->salida_fecha === today()->addDays(2)->format('Y-m-d'));
$rutaPlantilla->tramos()->update(['precio' => '999.00']);
$check($nueva->tramoPrecios()->first()->precio === '8.00');
$datos['tramos'][$clave]['precio'] = '9.50';
ProgramacionService::guardar($empresa->id, $datos, $nueva->id);
$check($nueva->tramoPrecios()->first()->precio === '9.50');
$rechazados = $datos;
$rechazados['tramos'][$clave]['hora_llegada'] = '00:00';
$reject(function () use ($empresa, $rechazados, $nueva) {
    ProgramacionService::guardar($empresa->id, $rechazados, $nueva->id);
}, Illuminate\Validation\ValidationException::class);
$check($nueva->tramoPrecios()->first()->precio === '9.50');
$reject(function () use ($empresaDos, $datos) {
    ProgramacionService::guardar($empresaDos->id, $datos);
}, Illuminate\Database\Eloquent\ModelNotFoundException::class);
$nueva->update(['estatus' => 3]);
$reject(function () use ($empresa, $datos, $nueva) {
    ProgramacionService::guardar($empresa->id, $datos, $nueva->id);
}, Illuminate\Validation\ValidationException::class);
$listado = new ListProgramacion;
$listado->boot();
$reject(function () use ($listado, $nueva) {
    $listado->changeStatus($nueva->id);
}, Illuminate\Validation\ValidationException::class);
$nueva->update(['estatus' => 1]);
$listado->changeStatus($nueva->id);
$check($nueva->fresh()->estatus === 2);
$listado->changeStatus($nueva->id);
$check($nueva->fresh()->estatus === 1);
$reject(function () use ($listado, $programacionDos) {
    $listado->changeStatus($programacionDos->id);
}, Illuminate\Database\Eloquent\ModelNotFoundException::class);
$listadoViajes = new ListViaje;
$listadoViajes->boot();
$listadoViajes->changeStatus($rutaPlantilla->id);
$check($rutaPlantilla->fresh()->estatus === 2);
$listadoViajes->changeStatus($rutaPlantilla->id);
$check($rutaPlantilla->fresh()->estatus === 1);
$reject(function () use ($empresa, $datos, $programacion) {
    ProgramacionService::guardar($empresa->id, $datos, $programacion->id);
}, Illuminate\Validation\ValidationException::class);
$check(str_contains(Livewire::test(SaveProgramacion::class)->set('viajeId', (string) $rutaPlantilla->id)->html(), 'Trayectos disponibles para vender'));
$check(str_contains(Livewire::test(ListProgramacion::class)->html(), 'Nuevo registro'));
$usuarioEmpresa->update(['es_admin' => 0]);
$reject(function () use ($listado, $nueva) {
    $listado->changeStatus($nueva->id);
}, Symfony\Component\HttpKernel\Exception\HttpException::class);
$reject(function () use ($listadoViajes, $rutaPlantilla) {
    $listadoViajes->changeStatus($rutaPlantilla->id);
}, Symfony\Component\HttpKernel\Exception\HttpException::class);
$usuarioEmpresa->update(['es_admin' => 1]);
echo "Programaciones: plantilla, medianoche, selección parcial, aislamiento, rollback, reservas y estatus OK\n";

$config = [
    'modo' => 'rango',
    'desde' => today()->addDay()->format('Y-m-d'),
    'hasta' => today()->addDays(10)->format('Y-m-d'),
    'dias' => [1, 2, 3, 4, 5, 6, 7],
    'fechas' => [],
];
$check(count(ProgramacionService::fechas($config)) === 10);
$config['dias'] = [1, 3, 5];
foreach (ProgramacionService::fechas($config) as $fecha) {
    $check(in_array(\Carbon\Carbon::parse($fecha)->dayOfWeekIso, [1, 3, 5], true));
}
$config['modo'] = 'especificas';
$config['hasta'] = null;
$config['fechas'] = [today()->addDays(10)->format('Y-m-d'), today()->addDay()->format('Y-m-d')];
$ids = ProgramacionService::guardarLote($empresa->id, $datos, $config);
$check(count($ids) === 2);
$primera = Programacion::with('tramoPrecios')->findOrFail($ids[0]);
$ultima = Programacion::with('tramoPrecios')->findOrFail($ids[1]);
$check($primera->getSalida()->format('Y-m-d H:i') === today()->addDays(2)->format('Y-m-d').' 00:30');
$check($ultima->getSalida()->format('Y-m-d H:i') === today()->addDays(11)->format('Y-m-d').' 00:30');
$check($ultima->tramoPrecios->first()->precio === '9.50');
$configDuplicado = $config;
$configDuplicado['fechas'][] = $configDuplicado['fechas'][0];
$reject(function () use ($configDuplicado) {
    ProgramacionService::fechas($configDuplicado);
}, Illuminate\Validation\ValidationException::class);
$configFuera = $config;
$configFuera['fechas'] = ['2100-12-31'];
$antes = Programacion::count();
$configFuera['fechas'] = [today()->addDays(10)->format('Y-m-d'), '2100-12-31'];
$reject(function () use ($empresa, $datos, $configFuera) {
    ProgramacionService::guardarLote($empresa->id, $datos, $configFuera);
}, Illuminate\Validation\ValidationException::class);
$check(Programacion::count() === $antes);
$html = Livewire::test(SaveProgramacion::class)->set('modoFechas', 'rango')->set('fechaHasta', today()->addDays(9)->format('Y-m-d'))->html();
$check(str_contains($html, 'Se crearán 10 programaciones.') && str_contains($html, 'Días de salida'));
echo "Lotes: rango, días seleccionados, fechas específicas, medianoche, duplicados y rollback completo OK\n";

$datosCupo = $datos;
$datosCupo['tramos'][$clave]['asientos_maximos_permitidos'] = 1;
$conCupo = ProgramacionService::guardar($empresa->id, $datosCupo);
$check($conCupo->tramoPrecios->first()->asientos_maximos_permitidos === 1);
$datosCupo['tramos'][$clave]['asientos_maximos_permitidos'] = $bus->total_asientos + 1;
$reject(function () use ($empresa, $datosCupo) { ProgramacionService::guardar($empresa->id, $datosCupo); }, Illuminate\Validation\ValidationException::class);
$datosCupo['tramos'][$clave]['asientos_maximos_permitidos'] = 0;
$reject(function () use ($empresa, $datosCupo) { ProgramacionService::guardar($empresa->id, $datosCupo); }, Illuminate\Validation\ValidationException::class);
echo "Puestos por trayecto: límite guardado y valores fuera de capacidad rechazados OK\n";

$rutaPlantilla->update(['estatus' => 2]);
$bus->update(['estatus' => 2]);
$check(Programacion::paraTaquilla($empresa->id, false)->whereKey($nueva->id)->exists());
$nueva->refresh()->load(['viaje', 'transporte']);
App\Services\Empresa\ReservaTaquillaService::validarSalida($nueva, $usuarioEmpresa->fresh(), $nueva->tramoPrecios()->first());
$reject(function () use ($empresa, $datos) { ProgramacionService::guardar($empresa->id, $datos); }, Illuminate\Database\Eloquent\ModelNotFoundException::class);
$rutaPlantilla->update(['estatus' => 1]);
$bus->update(['estatus' => 1]);
echo "Ruta y transporte inactivos: salidas previas vendibles y nuevas altas rechazadas OK\n";
