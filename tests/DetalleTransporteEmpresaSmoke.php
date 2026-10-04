<?php

require __DIR__.'/ListadosEmpresaSmoke.php';

use App\Livewire\Empresas\Transportes\DetailTransporte;
use App\Models\Transporte;
use Livewire\Livewire;
use Illuminate\Database\Eloquent\ModelNotFoundException;

$detalle = Livewire::test(DetailTransporte::class, ['transporte_id' => $bus->id]);
$html = $detalle->html();
$check(str_contains($html, 'Historial de programaciones'));
$check(str_contains($html, route('empresas.programaciones.detail', $programacion->id)));
$check(! str_contains($html, route('admin.programaciones.detail', $programacion->id)));
$ajeno = $bus->replicate();
$ajeno->empresa_id = $empresaDos->id;
$ajeno->placa = 'FOREIGN-DETAIL';
$ajeno->save();
$reject(function () use ($ajeno) {
    $componente = new DetailTransporte;
    $componente->boot();
    $componente->mount($ajeno->id);
}, ModelNotFoundException::class);
$bus->update(['estatus' => Transporte::ESTADO_INACTIVE]);
$detalle = Livewire::test(DetailTransporte::class, ['transporte_id' => $bus->id]);
$check(str_contains($detalle->html(), 'Historial de programaciones'));
echo "Detalle de transporte: render, navegación empresarial, aislamiento e historial de inactivos OK\n";

