<?php

require __DIR__.'/ListadosEmpresaSmoke.php';

use App\Livewire\Empresas\Viajes\SaveViaje;
use App\Models\Terminal;
use App\Models\Viaje;
use App\Models\ViajeTramo;
use App\Models\Programacion;
use App\Models\ProgramacionTramoPrecio;
use App\Services\Empresa\ViajeService;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

$paradas = [$terminales[0]->id, $terminales[1]->id];
foreach (['C', 'D', 'E'] as $nombre) {
    $paradas[] = Terminal::create([
        'estado_id' => $estado->id,
        'nombre' => 'Parada '.$nombre,
        'direccion' => 'Prueba',
        'latitud' => 0,
        'longitud' => 0,
        'estatus' => 1,
    ])->id;
}
$extra = array_pop($paradas);
$form = new SaveViaje;
$form->boot();
$form->mount();
$form->origenId = (string) $paradas[0];
$form->updatedOrigenId();
foreach (array_slice($paradas, 1) as $id) {
    $form->terminalId = (string) $id;
    $form->agregarParada();
}
$check(count($form->precios) === 6);
$check(array_keys($form->precios) === [
    $paradas[0].'-'.$paradas[1], $paradas[0].'-'.$paradas[2], $paradas[0].'-'.$paradas[3],
    $paradas[1].'-'.$paradas[2], $paradas[1].'-'.$paradas[3], $paradas[2].'-'.$paradas[3],
]);
$form->precios = array_fill_keys(array_keys($form->precios), '7.25');
$form->minutos = array_fill_keys(array_keys($form->minutos), 60);
$form->save();
$ruta = Viaje::where('empresa_id', $usuarioEmpresa->empresa_id)->latest('id')->firstOrFail();
$check($ruta->tramos()->count() === 6);
$check($ruta->secuenciaTerminales() === $paradas);
$check($ruta->tramosConsecutivos()->count() === 3);
$check($ruta->duracion_estimada === '03:00:00');
$check(ViajeTramo::precioBase($ruta, $paradas[0], $paradas[3]) === '7.25');

$editar = new SaveViaje;
$editar->boot();
$editar->mount($ruta->id);
$check($editar->paradas === $paradas);
$editar->terminalId = (string) $extra;
$editar->agregarParada();
$check(end($editar->paradas) === end($paradas));
$check($editar->paradas[3] === $extra);
$editar->precios = array_fill_keys(array_keys($editar->precios), '9.00');
$editar->minutos = array_fill_keys(array_keys($editar->minutos), 30);
$editar->save();
$check($ruta->fresh()->tramos()->count() === 10);
$idsAntes = $ruta->fresh()->tramos->pluck('id')->all();
$reject(function () use ($editar) { $editar->removerParada(3); }, ValidationException::class);
// Incluso enviando el recorrido anterior directamente, no se permite retirar la parada guardada.
$reject(function () use ($usuarioEmpresa, $ruta, $paradas, $editar) {
    ViajeService::guardar($usuarioEmpresa, $ruta->id, $paradas, $editar->precios, $editar->minutos, '', 1);
}, ValidationException::class);
$check($ruta->fresh()->tramos->pluck('id')->all() === $idsAntes);
$paradas = $editar->paradas;
$reject(function () use ($editar) { $editar->removerParada(0); }, ValidationException::class);
$reject(function () use ($editar) { $editar->removerParada(3); }, ValidationException::class);
$reject(function () use ($usuarioEmpresa, $ruta, $paradas, $editar) {
    $paradas[0] = $paradas[1];
    ViajeService::guardar($usuarioEmpresa, $ruta->id, $paradas, $editar->precios, $editar->minutos, '', 1);
}, ValidationException::class);
$reject(function () use ($usuarioEmpresa, $ruta, $paradas, $editar, $extra) {
    $paradas[0] = $extra;
    ViajeService::guardar($usuarioEmpresa, $ruta->id, $paradas, $editar->precios, $editar->minutos, '', 1);
}, ValidationException::class);
$ajeno = clone $usuarioEmpresa;
$ajeno->empresa_id = $empresaDos->id;
$reject(function () use ($ajeno, $ruta, $paradas, $editar) {
    ViajeService::guardar($ajeno, $ruta->id, $paradas, $editar->precios, $editar->minutos, '', 1);
}, ModelNotFoundException::class);

$salidaRuta = Programacion::create(['viaje_id' => $ruta->id, 'transporte_id' => $bus->id, 'asientos_totales' => 2, 'estatus' => 1]);
$tarifaRuta = ProgramacionTramoPrecio::create([
    'programacion_id' => $salidaRuta->id,
    'origen_terminal_id' => $paradas[0],
    'destino_terminal_id' => end($paradas),
    'precio' => '9.00',
]);
$editar->precios = array_fill_keys(array_keys($editar->precios), '12.00');
$editar->save();
$check($tarifaRuta->fresh()->precio === '9.00');
$reject(function () use ($editar) { $editar->removerParada(1); }, ValidationException::class);
array_splice($editar->paradas, 1, 1);
$editar->minutos = array_fill_keys(array_keys($editar->minutos), 30);
$reject(function () use ($editar) { $editar->save(); }, ValidationException::class);
$check($ruta->fresh()->secuenciaTerminales() === $paradas);
$check(count(App\Models\Terminal::obtenerSecuenciaRuta($salidaRuta)) === 5);

$sinPermiso = clone $usuarioEmpresa;
$sinPermiso->es_admin = 0;
$sinPermiso->id = 999999;
Illuminate\Support\Facades\Auth::guard('empresa')->setUser($sinPermiso);
$denegado = new SaveViaje;
$denegado->boot();
$denegado->mount();
$reject(function () use ($denegado) { $denegado->save(); }, Symfony\Component\HttpKernel\Exception\HttpException::class);
Illuminate\Support\Facades\Auth::guard('empresa')->setUser($usuarioEmpresa);
$html = Livewire\Livewire::mount(SaveViaje::class);
$check(str_contains($html, 'Precios base por trayecto'));
$html = Livewire\Livewire::mount(SaveViaje::class, ['viaje_id' => $ruta->id]);
$check(str_contains($html, '10 combinaciones'));
echo "SaveViaje: combinaciones, precios independientes, edición, extremos fijos, aislamiento, permisos, recorrido protegido y vistas OK\n";

// Verifica que la plantilla del seeder también genere todos los pares.
$seedEmpresa = $empresa->replicate();
$seedEmpresa->rif = 'SEED-RUTA-TEST';
$seedEmpresa->email = 'seed-ruta@test.test';
$seedEmpresa->save();
$seedEmpresa->tipo_entidad = App\Models\Empresa::AGENCIA_AUTOBUS;
$crearRutas = new ReflectionMethod(Database\Seeders\Test\EmpresasDemoSeeder::class, 'crearRutas');
$crearRutas->invoke(new Database\Seeders\Test\EmpresasDemoSeeder, $seedEmpresa, 1, Terminal::orderBy('id')->get());
foreach ($seedEmpresa->viajes as $rutaSeed) {
    $n = count($rutaSeed->secuenciaTerminales());
    $check($rutaSeed->tramos->count() === intdiv($n * ($n - 1), 2));
    $check($rutaSeed->tramos->every(function ($tramo) { return $tramo->precio !== null; }));
}
echo "Seeder de rutas: todas las combinaciones con precio y secuencia válida OK\n";

$detalle = new App\Livewire\Empresas\Viajes\DetailViaje;
$detalle->boot();
$detalle->mount($ruta->id);
$check($detalle->findViaje()->id === $ruta->id);
$check($detalle->render()->getData()['programaciones']->total() === 1);
$detalle->viaje_id = $seedEmpresa->viajes->first()->id;
$reject(function () use ($detalle) { $detalle->findViaje(); }, ModelNotFoundException::class);
$reject(function () use ($detalle) { $detalle->render(); }, ModelNotFoundException::class);
$html = Livewire\Livewire::mount(App\Livewire\Empresas\Viajes\DetailViaje::class, ['viaje_id' => $ruta->id]);
$check(str_contains($html, 'Matriz de Precios por Trayecto'));
$check(str_contains($html, '/empresa/operaciones/programaciones/detalle/'));
$check(! str_contains($html, '/admin/programaciones/'));
$adminDetalle = new App\Livewire\Admin\Viajes\DetailViaje;
$adminDetalle->viaje_id = $ruta->id;
$check($adminDetalle->findViaje()->tramos->count() === 10);
echo "DetailViaje: findViaje Admin/Empresas, aislamiento, historial paginado y vista empresarial OK\n";
