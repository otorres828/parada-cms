<?php

require __DIR__.'/TaquillaSmoke.php';

use App\Models\ProgramacionTramoPrecio;
use App\Models\Viaje;
use App\Models\ViajeTramo;
use App\Models\Terminal;
use App\Models\Programacion;
use App\Models\Reserva;
use App\Services\Empresa\ReservaTaquillaService;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

$reloj = now()->addDays(2)->startOfDay()->addMinutes(30);
Carbon::setTestNow($reloj);
try {
    $intermedia = Terminal::create([
        'estado_id' => $estado->id,
        'nombre' => 'Rodando intermedia nocturna',
        'direccion' => 'Calle',
        'latitud' => 0,
        'longitud' => 0,
    ]);
    $ruta = Viaje::create([
        'empresa_id' => $usuarioEmpresa->empresa_id,
        'origen_terminal_id' => $terminales[0]->id,
        'destino_terminal_id' => $terminales[1]->id,
        'duracion_estimada' => '03:00:00',
        'estatus' => 1,
    ]);
    foreach ([[$terminales[0]->id, $intermedia->id], [$intermedia->id, $terminales[1]->id]] as $orden => $extremos) {
        ViajeTramo::create([
            'viaje_id' => $ruta->id,
            'origen_terminal_id' => $extremos[0],
            'destino_terminal_id' => $extremos[1],
            'orden' => $orden + 1,
            'duracion_estimada' => '01:30:00',
        ]);
    }
    $salida = Programacion::create([
        'viaje_id' => $ruta->id,
        'transporte_id' => $programacion->transporte_id,
        'asientos_totales' => 4,
        'estatus' => 1,
    ]);
    $tramo = ProgramacionTramoPrecio::create([
        'programacion_id' => $salida->id,
        'origen_terminal_id' => $intermedia->id,
        'destino_terminal_id' => $terminales[1]->id,
        'fecha_salida' => $reloj->toDateString(),
        'hora_salida' => '01:00:00',
        'fecha_llegada' => $reloj->toDateString(),
        'hora_llegada' => '02:00:00',
        'precio' => '10.00',
    ]);
    $check(ProgramacionTramoPrecio::paraTaquilla($usuarioEmpresa->empresa_id, $reloj->toDateString())->whereKey($tramo->id)->exists());
    $check(! ProgramacionTramoPrecio::paraTaquilla($empresaDos->id, $reloj->toDateString())->whereKey($tramo->id)->exists());
    $venta = ReservaTaquillaService::registrar($usuarioEmpresa, $tramo->id, $comprador, [$persona], [['tipo' => 2, 'moneda' => 'USD', 'monto' => '10.00']]);
    $check($venta->estado_pago === Reserva::ESTADO_PAGO_PAGADO);
    $check($venta->tramoPrecio->getSalida()->format('H:i') === '01:00');
    $check($venta->tramoPrecio->getLlegada()->format('H:i') === '02:00');
    $nuevo = new App\Livewire\Empresas\Reservas\SaveReserva;
    $nuevo->boot();
    $nuevo->mount();
    $check($nuevo->render()->getData()['origenes']->contains('id', $intermedia->id));

    Carbon::setTestNow($reloj->copy()->setTime(23, 59));
    // Taquilla permite vender durante toda la fecha del tramo, aunque pasó la hora.
    $check(ProgramacionTramoPrecio::paraTaquilla($usuarioEmpresa->empresa_id, $reloj->toDateString())->whereKey($tramo->id)->exists());
    $ventaTardia = ReservaTaquillaService::registrar($usuarioEmpresa, $tramo->id, $comprador, [$persona], [['tipo' => 2, 'moneda' => 'USD', 'monto' => '10.00']]);
    $check($ventaTardia->estado_pago === Reserva::ESTADO_PAGO_PAGADO);
    $check($ventaTardia->pasajes->every(function ($pasaje) {
        return filled($pasaje->localizador) && $pasaje->getQr() !== null;
    }));
    $reject(function () use ($tramo) {
        $tramo->validarSalida();
    }, ValidationException::class);
    foreach ([Programacion::ESTADO_INACTIVO, Programacion::ESTADO_FINALIZADO] as $estatus) {
        $salida->estatus = $estatus;
        $reject(function () use ($salida, $usuarioEmpresa, $tramo) {
            ReservaTaquillaService::validarSalida($salida, $usuarioEmpresa, $tramo);
        }, ValidationException::class);
    }
    $salida->estatus = Programacion::ESTADO_PROGRAMADO;
    Carbon::setTestNow($reloj->copy()->addDay()->startOfDay());
    $reject(function () use ($salida, $usuarioEmpresa, $tramo) {
        ReservaTaquillaService::validarSalida($salida, $usuarioEmpresa, $tramo);
    }, ValidationException::class);
    $reject(function () use ($tramo) {
        $tramo->update(['hora_llegada' => '00:45:00']);
    }, ValidationException::class);
    $sinHorario = $tramo->fresh();
    $sinHorario->fecha_salida = null;
    $sinHorario->hora_salida = null;
    $check($sinHorario->getSalida() === null);
    $reject(function () use ($sinHorario) {
        $sinHorario->validarSalida();
    }, ValidationException::class);
    echo "Horarios por tramo: medianoche, origen intermedio, venta tardía en taquilla, fecha pasada y estados de programación OK\n";
} finally {
    Carbon::setTestNow();
}
