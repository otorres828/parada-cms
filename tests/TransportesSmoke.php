<?php

// Ejecuta el flujo existente y reutiliza únicamente su base SQLite en memoria.
require __DIR__.'/ReservaFlowSmoke.php';

$assert = function (bool $ok, string $message): void {
    if (! $ok) throw new RuntimeException($message);
};
$empresa->update(['tipo_entidad' => App\Models\Empresa::CONDUCTOR_CARRO]);
$assert($empresa->fresh()->getTipoEntidad() === 'Conductor de carro', 'Tipo de entidad no persistido');
$carro = App\Models\Transporte::create([
    'empresa_id' => $empresa->id, 'tipo_transporte' => 'carro', 'placa' => 'CAR-TEST',
    'modelo' => 'Sedán de prueba', 'tipo_asiento' => 'Normal', 'total_asientos' => 4,
    'estatus' => 1, 'es_plantilla' => false,
]);
$amenidad = App\Models\Amenidad::create(['nombre' => 'Aire acondicionado', 'icono' => 'bi-snow', 'estatus' => 1]);
$carro->amenidades()->attach($amenidad);
$assert($carro->fresh()->amenidades->first()->id === $amenidad->id, 'Relación de amenidades');
$assert(App\Models\Transporte::searchAdmin('', ['tipo_transporte'=>'carro'])->pluck('id')->all() === [$carro->id], 'Filtro carro');
$assert(! App\Models\Transporte::searchAdmin('', ['tipo_transporte'=>'autobus'])->whereKey($carro->id)->exists(), 'Filtro autobús');
$ventas = [];
foreach ([$bus, $carro] as $vehiculo) {
    $salida = $programacion->replicate();
    $salida->transporte_id = $vehiculo->id;
    $salida->fecha_salida = today()->addDays(2);
    $salida->save();
    $precio = $tarifa->replicate();
    $precio->programacion_id = $salida->id;
    $precio->save();
    $compra = App\Services\ReservaService::aplicarReserva($cliente, $precio->id);
    $prefix = $vehiculo->tipo_transporte === 'carro' ? 'CA' : 'AU';
    $assert((bool) preg_match('/^'.$prefix.'-[A-Z0-9]{10}$/', $compra->codigo_referencia), 'Formato del código '.$prefix);
    $compra = App\Services\ReservaService::agregarPasajero($cliente, $compra->id, $pasajero);
    $compra = App\Services\PagoReservaService::pasarAPendiente($cliente, $compra->id, $banco->id, 'TIPO-'.$compra->id, now()->toDateTimeString());
    $ventas[$vehiculo->tipo_transporte] = App\Services\PagoReservaService::confirmarPago($compra->id, $compra->monto_total);
}
foreach (['carro', 'autobus'] as $tipo) {
    $sales = App\Models\Reserva::salesReport(today()->toDateString(), today()->toDateString(), $tipo)->get();
    $companies = App\Models\Reserva::companiesReport(today()->toDateString(), today()->toDateString(), $tipo)->get();
    $assert((int) $sales->sum('cantidad') === 1, 'Reporte ventas '.$tipo);
    $assert((int) $companies->sum('cantidad') === 1, 'Reporte empresas '.$tipo);
    $assert((float) $sales->sum('total') === (float) $ventas[$tipo]->monto_total, 'Total filtrado '.$tipo);
}
$assert((int) App\Models\Reserva::salesReport(today()->toDateString(), today()->toDateString())->get()->sum('cantidad') === 2, 'Reporte sin filtro');
// El tipo se congela por reserva al emitir una orden, incluso cuando mezcla transportes.
App\Models\OrdenCobro::query()->delete();
$empresa->update(['tipo_contrato'=>App\Models\Empresa::CONTRATO_ELLOS_RECIBEN,'dia_corte'=>1,'dia_vencimiento'=>7,'hora_corte'=>'23:59:59']);
$ordenMixta = app(App\Services\OrdenCobroService::class)->generar($empresa, now()->addDay(), false);
$tipos = collect($ordenMixta->reservas_incluidas)->pluck('tipo_transporte')->unique()->sort()->values()->all();
$assert($tipos === ['autobus', 'carro'], 'Snapshot de orden mixta');
$carro->update(['tipo_transporte'=>'autobus']);
$assert(collect($ordenMixta->fresh()->reservas_incluidas)->contains('tipo_transporte', 'carro'), 'Snapshot no debe cambiar');
$carro->update(['tipo_transporte'=>'carro']);
// Renderiza pantallas Livewire y los detalles con los modelos reales.
$admin->update(['level'=>App\Models\Admin::ROOT, 'status'=>1]);
auth('admin')->setUser($admin->fresh());
foreach ([
    App\Livewire\Admin\Transportes\ListTransporte::class => [],
    App\Livewire\Admin\Transportes\DetailTransporte::class => ['transporte_id'=>$carro->id],
    App\Livewire\Admin\Empresas\SaveEmpresa::class => ['empresa_id'=>$empresa->id],
    App\Livewire\Admin\Empresas\ListEmpresa::class => [],
    App\Livewire\Admin\Reservas\ListReserva::class => [],
    App\Livewire\Admin\Reservas\DetailReserva::class => ['reserva_id'=>$ventas['carro']->id],
    App\Livewire\Admin\Pasajes\DetailPasaje::class => ['pasaje_id'=>$ventas['carro']->pasajes->first()->id],
    App\Livewire\Admin\Reembolsos\DetailReembolso::class => ['reembolso_id'=>$reembolso->id],
    App\Livewire\Admin\Reportes\SalesReport::class => [],
    App\Livewire\Admin\Reportes\CompaniesReport::class => [],
    App\Livewire\Admin\OrdenesCobro\DetailOrdenCobro::class => ['orden_cobro_id'=>$ordenMixta->id],
] as $component => $params) {
    $html = (string) Livewire\Livewire::mount($component, $params);
    $assert(strlen($html) > 100, 'Render '.$component);
    $assert(str_contains($html, 'Tipo de') || str_contains($html, 'Tipos de'), 'Etiqueta de tipo ausente en '.$component);
    if ($component === App\Livewire\Admin\Transportes\DetailTransporte::class) {
        $assert(str_contains($html, 'Aire acondicionado'), 'Amenidad no visible');
    }
}
// Prueba de migración con registros ya existentes, sin alterar su identidad ni relaciones.
$migration = require __DIR__.'/../database/migrations/2026_09_27_000001_convert_autobuses_to_transportes.php';
$count = App\Models\Transporte::count();
$migration->down();
$assert(Illuminate\Support\Facades\DB::table('autobuses')->count() === $count, 'Rollback conserva vehículos');
$migration->up();
$assert(App\Models\Transporte::count() === $count, 'Migración conserva vehículos');
$assert(App\Models\Transporte::find($carro->id)->amenidades()->whereKey($amenidad->id)->exists(), 'Migración conserva amenidades');
$assert($ventas['carro']->fresh()->programacion->transporte_id === $carro->id, 'Migración conserva FK');
echo "OK: tipos, filtros, reportes, prefijos, órdenes mixtas, pantallas y migración con datos.\n";
