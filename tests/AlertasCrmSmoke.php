<?php
require __DIR__.'/ListadosEmpresaSmoke.php';
use App\Livewire\Empresas\Cupones\SaveCampana;
use App\Livewire\Empresas\Cupones\ListCampana;
use Livewire\Livewire;
session()->put('empresas_campana_success', 'Campaña guardada una sola vez');
session()->put('admin_amenidad_success', 'Mensaje de otro módulo');
Livewire::test(SaveCampana::class)->html();
$check(session()->has('empresas_campana_success'));
$html = Livewire::test(ListCampana::class)->html();
$check(! session()->has('empresas_campana_success'));
$check(session()->has('admin_amenidad_success'));
$html = Livewire::test(ListCampana::class)->html();
$check(! str_contains($html, 'Campaña guardada una sola vez'));
echo "Alertas: Save conserva flash, listado consume una vez y módulos aislados OK\n";
