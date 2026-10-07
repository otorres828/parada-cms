<?php

require __DIR__.'/ListadosEmpresaSmoke.php';

use App\Livewire\Empresas\Usuarios\ListUsuarioEmpresa;
use App\Livewire\Empresas\Usuarios\SaveUsuarioEmpresa;
use App\Models\PermissionEmpresa;
use App\Models\UsuarioEmpresa;
use Database\Seeders\GroupSectionPermissionEmpresaSeeder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Symfony\Component\HttpKernel\Exception\HttpException;

(new GroupSectionPermissionEmpresaSeeder)->run();
auth('empresa')->setUser($usuarioEmpresa->fresh());
$permiso = PermissionEmpresa::whereHas('section', function ($query) {
    $query->where('url', 'reservas');
})->where('url', 'list')->firstOrFail();
$reservado = PermissionEmpresa::whereHas('section', function ($query) {
    $query->where('url', 'usuarios');
})->firstOrFail();

$form = Livewire::test(SaveUsuarioEmpresa::class);
$check(! str_contains($form->html(), 'name="es_admin"'));
$form->set('nombre', 'Operador prueba')->set('email', 'operador@empresa.test')
    ->set('password', 'ClaveSegura123')->set('selectedPermissions', [(string) $permiso->id])->call('save');
$operador = UsuarioEmpresa::where('email', 'operador@empresa.test')->firstOrFail();
$check($operador->empresa_id === $empresa->id && ! $operador->isAdmin());
$check(Hash::check('ClaveSegura123', $operador->password));
$check($operador->hasPermission('reservas', 'list'));

$hash = $operador->password;
$edit = Livewire::test(SaveUsuarioEmpresa::class, ['usuario_empresa_id' => $operador->id]);
$edit->set('nombre', 'Operador editado')->set('selectedPermissions', [])->call('save');
$check($operador->fresh()->nombre === 'Operador editado' && $operador->fresh()->password === $hash);
$check(! $operador->fresh()->isAdmin() && $operador->fresh()->permisos()->count() === 0);
$edit->set('password', 'OtraClaveSegura123')->call('save');
$check(Hash::check('OtraClaveSegura123', $operador->fresh()->password));

$ajeno = UsuarioEmpresa::create([
    'empresa_id' => $empresaDos->id,
    'nombre' => 'Usuario ajeno',
    'email' => 'ajeno@empresa.test',
    'password' => 'ClaveSegura123',
    'es_admin' => 0,
    'estatus' => 1,
]);
$list = Livewire::test(ListUsuarioEmpresa::class);
$check($list->viewData('usuarios')->getCollection()->every(function ($usuario) { return ! $usuario->isAdmin(); }));
$list->set('search', $usuarioEmpresa->email);
$check($list->viewData('usuarios')->total() === 0);
$list->set('search', '');
$check(str_contains($list->html(), 'Operador editado') && ! str_contains($list->html(), 'Usuario ajeno'));
$list->set('search', 'Usuario ajeno');
$check(str_contains($list->html(), 'No se encontraron registros.'));
$list->set('search', '')->call('changeStatus', $operador->id);
$check($operador->fresh()->estatus === UsuarioEmpresa::ESTADO_INACTIVE);
$list->set('status', '2');
$check(str_contains($list->html(), 'Operador editado'));
$reject(function () use ($ajeno) {
    $form = new SaveUsuarioEmpresa;
    $form->boot();
    $form->mount($ajeno->id);
}, ModelNotFoundException::class);
$reject(function () use ($ajeno) {
    $list = new ListUsuarioEmpresa;
    $list->boot();
    $list->changeStatus($ajeno->id);
}, ModelNotFoundException::class);
$reject(function () use ($usuarioEmpresa) {
    $list = new ListUsuarioEmpresa;
    $list->boot();
    $list->changeStatus($usuarioEmpresa->id);
}, ModelNotFoundException::class);

$invalid = new SaveUsuarioEmpresa;
$invalid->boot();
$invalid->mount();
$invalid->nombre = 'Inválido';
$invalid->email = 'invalido@empresa.test';
$invalid->password = 'ClaveSegura123';
$invalid->selectedPermissions = [$reservado->id];
$reject(function () use ($invalid) { $invalid->save(); }, ValidationException::class);
$check(! UsuarioEmpresa::where('email', 'invalido@empresa.test')->exists());

$propio = new SaveUsuarioEmpresa;
$propio->boot();
$propio->mount($usuarioEmpresa->id);
$propio->estatus = 2;
$reject(function () use ($propio) { $propio->save(); }, ValidationException::class);
$check($usuarioEmpresa->fresh()->isAdmin() && $usuarioEmpresa->fresh()->estatus === 1);

$eliminable = UsuarioEmpresa::create([
    'empresa_id' => $empresa->id,
    'nombre' => 'Usuario eliminable',
    'email' => 'eliminable@empresa.test',
    'password' => 'ClaveSegura123',
    'es_admin' => 0,
    'estatus' => 1,
]);
$list->set('status', '')->set('search', 'Usuario eliminable');
$check(str_contains($list->html(), 'confirmDeletion'));
$list->call('deleteUsuario', $eliminable->id);
$check($eliminable->fresh()->estatus === UsuarioEmpresa::ESTADO_DELETE);
$check(! UsuarioEmpresa::searchAdmin()->whereKey($eliminable->id)->exists());
$check(UsuarioEmpresa::searchUserName($eliminable->email) === null);
$check(str_contains($list->html(), 'No se encontraron registros.'));
foreach ([$ajeno->id, $usuarioEmpresa->id] as $idProtegido) {
    $reject(function () use ($idProtegido) {
        $list = new ListUsuarioEmpresa;
        $list->boot();
        $list->deleteUsuario($idProtegido);
    }, ModelNotFoundException::class);
}

// Ni un permiso explícito de usuarios habilita este módulo sin es_admin.
$operador->update(['estatus' => 1]);
$operador->permisos()->attach($reservado->id);
auth('empresa')->setUser($operador->fresh());
$check(! $operador->fresh()->hasPermission('usuarios', $reservado->url));
$check(! (new App\View\Components\Layout\Sidebar\AdministrationMenuEmpresa)->listUsuarios);
foreach ([ListUsuarioEmpresa::class, SaveUsuarioEmpresa::class] as $class) {
    $reject(function () use ($class) {
        $component = new $class;
        $component->boot();
    }, HttpException::class);
}
$reject(function () use ($invalid) { $invalid->save(); }, HttpException::class);
$reject(function () use ($list, $operador) {
    $list->instance()->deleteUsuario($operador->id);
}, HttpException::class);
foreach (['list', 'add', 'edit'] as $action) {
    $request = \Illuminate\Http\Request::create('/empresa/administracion/usuarios');
    $route = new \Illuminate\Routing\Route('GET', '/empresa/administracion/usuarios', function () {});
    $route->name('empresas.usuarios.'.$action);
    $request->setRouteResolver(function () use ($route) { return $route; });
    $reject(function () use ($request) {
        (new \App\Http\Middleware\CheckPermissionEmpresa)->handle($request, function () {
            throw new RuntimeException('No debe permitirse el acceso a Usuarios');
        });
    }, HttpException::class);
}

echo "Usuarios Empresa: alta sin administrador, edición, hash, permisos, estados, aislamiento y acceso exclusivo OK\n";
