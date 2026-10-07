<?php

require __DIR__.'/ListadosEmpresaSmoke.php';

use App\Livewire\Empresas\Cupones\SaveCampana;
use App\Models\ConfiguracionCupon;
use App\Models\UsuarioEmpresa;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Livewire\Livewire;

$form = Livewire::test(SaveCampana::class);
$check(! str_contains($form->html(), 'name="empresa_id"'));
$form->set('nombre_campana', 'Campaña de prueba empresa')
    ->set('cantidad_generar', 3)->set('monto_descuento', '10.00')
    ->set('fecha_inicio', now()->format('Y-m-d\TH:i'))
    ->set('fecha_fin', now()->addDays(7)->format('Y-m-d\TH:i'))->call('save');
$campana = ConfiguracionCupon::where('nombre_campana', 'CAMPAÑADEPRUEBAEMPRESA')->firstOrFail();
$check($campana->empresa_id === $empresa->id);
$check($campana->cupones()->count() === 3);
$edit = Livewire::test(SaveCampana::class, ['configuracion_cupon_id' => $campana->id]);
$edit->set('nombre_campana', 'Campaña actualizada')->set('monto_descuento', '50.00')->call('save');
$check($campana->fresh()->nombre_campana === 'CAMPAÑAACTUALIZADA');
$check((float) $campana->fresh()->monto_descuento === 10.0);
$ajena = $campana->replicate();
$ajena->empresa_id = $empresaDos->id;
$ajena->save();
$reject(function () use ($ajena) {
    $form = new SaveCampana;
    $form->boot();
    $form->mount($ajena->id);
}, ModelNotFoundException::class);
$form = new SaveCampana;
$form->boot();
$form->mount();
$form->nombre_campana = 'Inválida';
$form->monto_descuento = '101';
$form->fecha_inicio = now()->format('Y-m-d\TH:i');
$form->fecha_fin = now()->addDay()->format('Y-m-d\TH:i');
$reject(function () use ($form) { $form->save(); }, ValidationException::class);
$usuarioSinPermisos = UsuarioEmpresa::create([
    'empresa_id' => $empresa->id,
    'nombre' => 'Sin permisos',
    'email' => 'cupones-sin-permisos@test.local',
    'password' => bcrypt('test-password'),
    'es_admin' => 0,
    'estatus' => 1,
]);
Auth::guard('empresa')->setUser($usuarioSinPermisos);
$reject(function () {
    $form = new SaveCampana;
    $form->boot();
    $form->mount();
    $form->save();
}, HttpException::class);
echo "Campañas Empresa: alta, cupones generados, edición, restricciones, aislamiento y permisos OK\n";
