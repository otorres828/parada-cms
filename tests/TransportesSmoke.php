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
$viajeroTransporte = App\Services\ViajeroService::agregarViajero($cliente, array_merge($pasajero, [
    'nombre' => 'Viajero transporte',
    'documento_identidad' => 'V-87654321',
]));
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
    $compra = App\Services\ReservaService::agregarPasajero($cliente, $compra->id, $viajeroTransporte->id);
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
foreach (['', 'carro', 'autobus'] as $tipo) {
    $dashboard = new App\Livewire\Admin\Dashboard;
    $dashboard->date_from = today()->toDateString();
    $dashboard->date_to = today()->toDateString();
    $dashboard->tipo_transporte = $tipo;
    $data = $dashboard->render()->getData();
    $salidasEsperadas = App\Models\Programacion::upcomingForDashboard(today()->toDateString(), today()->addDays(6)->toDateString())
        ->get()->filter(fn ($salida) => $tipo === '' || $salida->transporte->tipo_transporte === $tipo);
    $assert($data['metrics']['salidas'] === $salidasEsperadas->count(), 'Dashboard contador de salidas '.$tipo);
    $assert($data['proximasSalidas']->modelKeys() === $salidasEsperadas->take(5)->values()->modelKeys(), 'Dashboard próximas salidas '.$tipo);
    $filters = ['date_from' => $dashboard->date_from, 'date_to' => $dashboard->date_to];
    $expected = App\Models\Reserva::searchAdmin('', $filters)->get()
        ->filter(fn ($reserva) => $tipo === '' || $reserva->programacion->transporte->tipo_transporte === $tipo);
    $assert($data['totalReservas'] === $expected->count(), 'Dashboard conteos '.$tipo);
    $assert($data['ultimasReservas']->every(fn ($reserva) => $expected->contains('id', $reserva->id)), 'Dashboard reservas '.$tipo);
    $reservasContabilizadasComoPagadas = $expected->whereIn('estado_pago', [
        App\Models\Reserva::ESTADO_PAGO_PAGADO,
        App\Models\Reserva::ESTADO_PAGO_REEMBOLSADO,
        App\Models\Reserva::ESTADO_PAGO_REPROGRAMADO,
    ]);
    $assert(
        $data['metrics']['pasajes'] === App\Models\Pasaje::whereIn(
            'reserva_id',
            $reservasContabilizadasComoPagadas->modelKeys(),
        )->count(),
        'Dashboard pasajes '.$tipo,
    );
}
foreach ([
    App\Livewire\Admin\Dashboard::class => [],
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
    if ($component === App\Livewire\Admin\Dashboard::class) {
        $assert(str_contains($html, 'wire:model.live="tipo_transporte"'), 'Filtro de transporte del dashboard');
    } elseif (in_array($component, [
        App\Livewire\Admin\Transportes\ListTransporte::class,
        App\Livewire\Admin\Transportes\DetailTransporte::class,
        App\Livewire\Admin\Empresas\SaveEmpresa::class,
        App\Livewire\Admin\Empresas\ListEmpresa::class,
        App\Livewire\Admin\Reportes\SalesReport::class,
        App\Livewire\Admin\Reportes\CompaniesReport::class,
    ], true)) {
        $assert(str_contains($html, 'Tipo de') || str_contains($html, 'Tipos de'), 'Etiqueta de tipo ausente en '.$component);
    }
    if ($component === App\Livewire\Admin\Transportes\DetailTransporte::class) {
        $assert(str_contains($html, 'Aire acondicionado'), 'Amenidad no visible');
    }
}
// Las migraciones originales crean directamente el esquema final.
$assert(Illuminate\Support\Facades\Schema::hasTable('transportes'), 'Tabla transportes');
$assert(Illuminate\Support\Facades\Schema::hasTable('amenidad_transporte'), 'Tabla amenidades');
$assert(! Illuminate\Support\Facades\Schema::hasTable('autobuses'), 'No debe existir la tabla antigua');
$assert(Illuminate\Support\Facades\Schema::hasColumn('programaciones', 'transporte_id'), 'FK transporte');
$assert(Illuminate\Support\Facades\Schema::hasColumn('empresas', 'tipo_entidad'), 'Tipo entidad');
echo "OK: tipos, filtros, reportes, prefijos, órdenes mixtas, pantallas y esquema original.\n";
