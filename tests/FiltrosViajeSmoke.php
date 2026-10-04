<?php

require __DIR__.'/ListadosEmpresaSmoke.php';

$filtro = new App\Livewire\Empresas\Viajes\SaveViaje;
$filtro->boot();
$filtro->mount();
$filtro->origenId = (string) $terminales[0]->id;
$filtro->updatedOrigenId();
$filtro->terminalId = (string) $terminales[1]->id;
$filtro->agregarParada();
$recorrido = $filtro->paradas;
$otroEstado = App\Models\Estado::create(['nombre' => 'Otro estado']);
$filtro->terminalId = (string) $terminales[1]->id;
$filtro->estadoParadaId = (string) $otroEstado->id;
$filtro->updatedEstadoParadaId();
$check($filtro->terminalId === '');
$check($filtro->paradas === $recorrido);
$check($filtro->render()->getData()['terminalesParada']->isEmpty());
$check($filtro->render()->getData()['terminales']->has($terminales[0]->id));
$filtro->estadoOrigenId = (string) $estado->id;
$filtro->updatedEstadoOrigenId();
$check($filtro->origenId === (string) $terminales[0]->id);
$filtro->estadoOrigenId = (string) $otroEstado->id;
$filtro->updatedEstadoOrigenId();
$check($filtro->origenId === '' && $filtro->paradas === $recorrido);
$filtro->estadoParadaId = '';
$filtro->updatedEstadoParadaId();
$check($filtro->render()->getData()['terminalesParada']->count() >= 2);
$html = Livewire\Livewire::mount(App\Livewire\Empresas\Viajes\SaveViaje::class);
$check(str_contains($html, 'viaje-estado-origen') && str_contains($html, 'viaje-estado-parada'));
$html = Livewire\Livewire::mount(App\Livewire\Empresas\Viajes\SaveViaje::class, ['viaje_id' => $viaje->id]);
$check(! str_contains($html, 'id="viaje-parada"'));
echo "Filtros de estado: opciones, limpieza de selección, conservación del recorrido y vistas OK\n";
