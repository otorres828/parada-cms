<?php

require __DIR__.'/ListadosEmpresaSmoke.php';

use App\Livewire\Empresas\Transportes\SaveTransporte;
use App\Models\Amenidad;
use App\Models\Transporte;
use Livewire\Livewire;

$amenidadActiva = Amenidad::create(['nombre' => 'Wifi', 'icono' => 'bi-wifi', 'estatus' => 1]);
$amenidadInactiva = Amenidad::create(['nombre' => 'TV', 'icono' => 'bi-tv', 'estatus' => 2]);
$form = Livewire::test(SaveTransporte::class);
$check(str_contains($form->html(), 'Amenidades del transporte'));
$form->set('placa', ' ab123 ')->set('modelo', 'Yutong')->set('tipo_asiento', 'Reclinable')
    ->set('total_asientos', 40)->set('amenidadesSeleccionadas', [$amenidadActiva->id])->call('save');
$registrado = Transporte::where('placa', 'AB123')->firstOrFail();
$check($registrado->empresa_id === $empresa->id && $registrado->tipo_transporte === $empresa->getTipoTransporte());
$check(! $registrado->es_plantilla && $registrado->amenidades->pluck('id')->all() === [$amenidadActiva->id]);
$antes = Transporte::count();
$form = new SaveTransporte;
$form->boot();
$form->mount();
$form->placa = 'AB123';
$form->modelo = 'Otro';
$form->tipo_asiento = 'Normal';
$reject(function () use ($form) { $form->save(); }, Illuminate\Validation\ValidationException::class);
$form->placa = 'BC456';
$form->amenidadesSeleccionadas = [$amenidadInactiva->id];
$reject(function () use ($form) { $form->save(); }, Illuminate\Validation\ValidationException::class);
$check(Transporte::count() === $antes);
$form->amenidadesSeleccionadas = [];
$usuarioEmpresa->update(['es_admin' => 0]);
$reject(function () use ($form) { $form->save(); }, Symfony\Component\HttpKernel\Exception\HttpException::class);
$usuarioEmpresa->update(['es_admin' => 1]);
echo "Alta de transporte: render, empresa y tipo propios, amenidades, placa duplicada y permisos OK\n";


$editar = new SaveTransporte;
$editar->boot();
$editar->mount($registrado->id);
$check($editar->amenidadesSeleccionadas === [$amenidadActiva->id]);
$editar->modelo = 'Modelo actualizado';
$editar->amenidadesSeleccionadas = [];
$editar->save();
$check($registrado->fresh()->modelo === 'Modelo actualizado' && $registrado->amenidades()->count() === 0);
$listado = new App\Livewire\Empresas\Transportes\ListTransporte;
$listado->boot();
$listado->changeStatus($registrado->id);
$check($registrado->fresh()->estatus === 2);
$listado->changeStatus($registrado->id);
$check($registrado->fresh()->estatus === 1);
$ajeno = $registrado->replicate();
$ajeno->empresa_id = $empresaDos->id;
$ajeno->placa = 'OTRA';
$ajeno->save();
$reject(function () use ($listado, $ajeno) { $listado->changeStatus($ajeno->id); }, Illuminate\Database\Eloquent\ModelNotFoundException::class);
$reject(function () use ($editar, $ajeno) { $editar->mount($ajeno->id); }, Illuminate\Database\Eloquent\ModelNotFoundException::class);
$editar->mount($bus->id);
$editar->placa = 'BUS-FIJO';
$editar->total_asientos = 10;
$editar->save();
$check($bus->fresh()->total_asientos === 10);
$check($programacion->fresh()->asientos_totales === 2);
$usuarioEmpresa->update(['es_admin' => 0]);
$reject(function () use ($listado, $registrado) { $listado->changeStatus($registrado->id); }, Symfony\Component\HttpKernel\Exception\HttpException::class);
$usuarioEmpresa->update(['es_admin' => 1]);
echo "Transportes: edición, amenidades, estatus, aislamiento, permisos y capacidad editable sin modificar programaciones anteriores OK\n";
