<?php

require __DIR__.'/ListadosEmpresaSmoke.php';

use App\Models\Reserva;
use App\Models\Pasaje;
use App\Models\PagoReserva;
use App\Services\Empresa\ReprogramacionService;
use App\Livewire\Empresas\Reprogramaciones\SaveReprogramacion;
use App\Livewire\Empresas\Reprogramaciones\ListReprogramacion;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Livewire\Livewire;

$crearOriginal = function () use ($r) {
    $original = $r->replicate();
    $original->fill(['estado_pago' => Reserva::ESTADO_PAGO_PAGADO, 'reprogramacion_id' => null, 'codigo_referencia' => Reserva::generarLocalizador(7), 'monto_pasajes' => '10.00', 'descuento_aplicado' => '0.00', 'tasa_servicio' => '1.00', 'monto_total' => '11.00']);
    $original->save();
    $pasaje = $r->pasajes()->firstOrFail()->replicate();
    $pasaje->fill(['reserva_id' => $original->id, 'numero_asiento' => 1, 'precio_base' => '10.00', 'descuento' => '0.00', 'subtotal' => '10.00', 'tasa_servicio' => '1.00', 'total' => '11.00', 'abordado' => false, 'hora_abordaje' => null, 'localizador' => null]);
    $pasaje->save();

    return $original;
};
$nuevaProgramacion = $programacion->replicate();
$nuevaProgramacion->asientos_totales = 40;
$nuevaProgramacion->estatus = 1;
$nuevaProgramacion->save();
$nuevaTarifa = $tarifa->replicate();
$nuevaTarifa->programacion_id = $nuevaProgramacion->id;
$nuevaTarifa->precio = '10.00';
$nuevaTarifa->save();
$original = $crearOriginal();
$nueva = ReprogramacionService::registrar($usuarioEmpresa, $original->id, $nuevaTarifa->id, [], 'RP-TEST001');
$check($original->fresh()->estado_pago === Reserva::ESTADO_PAGO_REPROGRAMADO);
$check($nueva->estado_pago === Reserva::ESTADO_PAGO_PAGADO && $nueva->reprogramacion_id === $original->id);
$check((float) $nueva->tasa_servicio === 0.0 && $nueva->pagos()->count() === 0);
$check($nueva->pasajes->first()->viajero === $original->pasajes()->first()->viajero);
$check($nueva->pasajes->first()->getQr() !== null && $original->pasajes()->first()->getQr() === null);
$check(ReprogramacionService::registrar($usuarioEmpresa, $original->id, $nuevaTarifa->id, [], 'RP-TEST001')->id === $nueva->id);
$original = $crearOriginal();
$nuevaTarifa->update(['precio' => '15.00']);
$antes = Reserva::count();
$reject(function () use ($usuarioEmpresa, $original, $nuevaTarifa) {
    ReprogramacionService::registrar($usuarioEmpresa, $original->id, $nuevaTarifa->id, [], 'RP-FAIL001');
}, ValidationException::class);
$check(Reserva::count() === $antes && $original->fresh()->estado_pago === Reserva::ESTADO_PAGO_PAGADO);
$nueva = ReprogramacionService::registrar($usuarioEmpresa, $original->id, $nuevaTarifa->id, [['tipo' => PagoReserva::TIPO_PAGO_EFECTIVO, 'moneda' => 'USD', 'monto' => '5.00']], 'RP-DIFF001');
$check((float) $nueva->pagos()->sum('total') === 5.0 && (float) $nueva->monto_total === 15.0);
$original = $crearOriginal();
$nuevaTarifa->update(['precio' => '9.00']);
$reject(function () use ($usuarioEmpresa, $original, $nuevaTarifa) {
    ReprogramacionService::registrar($usuarioEmpresa, $original->id, $nuevaTarifa->id, [], 'RP-LOW001');
}, ValidationException::class);
$original->pasajes()->update(['abordado' => true]);
$reject(function () use ($original) { ReprogramacionService::validarOriginal($original->fresh(['pasajes', 'tramoPrecio'])); }, ValidationException::class);
$original->pasajes()->update(['abordado' => false]);
$original->tramoPrecio->update(['fecha_salida' => today()->subDays(2), 'hora_salida' => '01:00:00', 'fecha_llegada' => today()->subDays(2), 'hora_llegada' => '02:00:00']);
$reject(function () use ($original) { ReprogramacionService::validarOriginal($original->fresh(['pasajes', 'tramoPrecio'])); }, ValidationException::class);
$reject(function () use ($empresaDos, $original) { ReprogramacionService::original($empresaDos->id, $original->id); }, ModelNotFoundException::class);
$check(str_contains(Livewire::test(SaveReprogramacion::class)->html(), 'Nueva reprogramación'));
$check(str_contains(Livewire::test(ListReprogramacion::class)->html(), 'RP-TEST001'));
echo "Reprogramaciones: mismo importe, diferencia, QR, historial, rollback, plazo, abordados, aislamiento y vistas OK\n";

$tarifa->update(['fecha_salida' => today()->addDay(), 'hora_salida' => '18:00:00', 'fecha_llegada' => today()->addDay(), 'hora_llegada' => '19:00:00']);
$nuevaTarifa->update(['precio' => '10.00']);
$originalFormulario = $crearOriginal();
$originalFormulario->tramoPrecio->update(['fecha_salida' => today()->addDay(), 'hora_salida' => '18:00:00', 'fecha_llegada' => today()->addDay(), 'hora_llegada' => '19:00:00']);
$formulario = Livewire::test(SaveReprogramacion::class)->set('reservaId', (string) $originalFormulario->id)
    ->set('fecha', $nuevaTarifa->fecha_salida->toDateString())->set('tarifaId', (string) $nuevaTarifa->id);
$check(str_contains($formulario->html(), 'Resumen de la reprogramación'));
$check(str_contains($formulario->html(), 'Registrar reprogramación'));
$formulario->call('save');
$check($originalFormulario->fresh()->estado_pago === Reserva::ESTADO_PAGO_REPROGRAMADO);
echo "Formulario Livewire: selección, cotización y registro confirmado OK\n";

$nuevaProgramacion->update(['asientos_totales' => 1]);
$sinCupo = $crearOriginal();
$reject(function () use ($usuarioEmpresa, $sinCupo, $nuevaTarifa) {
    ReprogramacionService::registrar($usuarioEmpresa, $sinCupo->id, $nuevaTarifa->id, [], 'RP-NOCUPO');
}, ValidationException::class);
$check($sinCupo->fresh()->estado_pago === Reserva::ESTADO_PAGO_PAGADO);
$nuevaProgramacion->update(['asientos_totales' => 40]);
$sinPermiso = clone $usuarioEmpresa;
$sinPermiso->es_admin = 0;
$reject(function () use ($sinPermiso, $sinCupo, $nuevaTarifa) {
    ReprogramacionService::registrar($sinPermiso, $sinCupo->id, $nuevaTarifa->id, [], 'RP-NOPER');
}, Symfony\Component\HttpKernel\Exception\HttpException::class);
echo "Reprogramaciones: falta de cupo y permiso rechazadas sin alterar reserva original OK\n";

$plazo = $crearOriginal();
$plazo->tramoPrecio->update(['fecha_salida' => today()->subDay(), 'hora_salida' => '00:01:00', 'fecha_llegada' => today()->subDay(), 'hora_llegada' => '01:00:00']);
ReprogramacionService::validarOriginal($plazo->fresh(['pasajes', 'tramoPrecio']));
$plazo->tramoPrecio->update(['fecha_salida' => today()->subDays(2), 'fecha_llegada' => today()->subDays(2)]);
$reject(function () use ($plazo) { ReprogramacionService::validarOriginal($plazo->fresh(['pasajes', 'tramoPrecio'])); }, ValidationException::class);
echo "Plazo por fecha: viaje de ayer permitido sin importar hora; anteayer rechazado OK\n";

$reprogramada = Reserva::whereNotNull('reprogramacion_id')->where('estado_pago', Reserva::ESTADO_PAGO_PAGADO)->firstOrFail();
$mensajeReprogramada = '';
try {
    ReprogramacionService::validarOriginal($reprogramada->load(['pasajes', 'tramoPrecio']));
} catch (ValidationException $exception) {
    $mensajeReprogramada = collect($exception->errors())->flatten()->first();
}
$reject(function () use ($reprogramada) {
    ReprogramacionService::validarOriginal($reprogramada->load(['pasajes', 'tramoPrecio']));
}, ValidationException::class);
$form = Livewire::test(SaveReprogramacion::class)->set('searchReserva', $reprogramada->codigo_referencia);
$check(! str_contains($form->html(), '<option value="'.$reprogramada->id.'">'));
echo "Reservas con reprogramacion_id: excluidas de búsqueda y rechazadas por el servicio OK\n";

$form = Livewire::test(SaveReprogramacion::class)->set('searchReserva', $reprogramada->codigo_referencia);
$check($mensajeReprogramada !== '' && str_contains($form->html(), $mensajeReprogramada));
$check($form->get('reservas')->isEmpty());
$form->set('searchReserva', 'CODIGO-INEXISTENTE-TEST');
$check(str_contains($form->html(), 'No se encontró una reserva de tu empresa'));
$form->set('searchReserva', '');
$check(! str_contains($form->html(), 'No se encontró una reserva de tu empresa'));
echo "Búsqueda: motivo de rechazo, inexistente y limpieza de alerta OK\n";

$buscable = $crearOriginal();
$buscable->update(['programacion_id' => $nuevaProgramacion->id, 'programacion_tramo_precio_id' => $nuevaTarifa->id]);
$form->set('searchReserva', $buscable->codigo_referencia);
$check($form->get('reservas')->modelKeys() === [$buscable->id]);
$check($form->errors()->isEmpty());
$form->set('searchReserva', '');
$check($form->get('reservas')->isEmpty());
echo "Colección de reservas: búsqueda válida y limpieza al borrar el texto OK\n";
