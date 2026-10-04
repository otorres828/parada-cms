<?php

require __DIR__.'/ListadosEmpresaSmoke.php';

use App\Livewire\Empresas\PoliticasEmbarque\SavePoliticaEmbarque;
use Livewire\Livewire;

$empresa->update(['politicas' => '<p>Contenido inicial</p>']);
$ajenas = $empresaDos->politicas;
$form = Livewire::test(SavePoliticaEmbarque::class);
$check(str_contains($form->html(), 'politicas-embarque-empresa'));
$check($form->get('contenido') === '<p>Contenido inicial</p>');
$form->set('contenido', '<h2>Embarque</h2><p>Presentarse antes de la salida.</p>')->call('save');
$check($empresa->fresh()->politicas === '<h2>Embarque</h2><p>Presentarse antes de la salida.</p>');
$check($empresaDos->fresh()->politicas === $ajenas);
$clase = new SavePoliticaEmbarque;
$clase->boot();
$clase->mount();
$clase->contenido = '';
$reject(function () use ($clase) { $clase->save(); }, Illuminate\Validation\ValidationException::class);
$usuarioEmpresa->update(['es_admin' => 0]);
$clase->contenido = '<p>Sin permiso</p>';
$reject(function () use ($clase) { $clase->save(); }, Symfony\Component\HttpKernel\Exception\HttpException::class);
echo "Políticas de embarque: editor renderizado, carga, guardado, aislamiento y permisos OK\n";
