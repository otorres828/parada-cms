<?php

require __DIR__.'/ListadosEmpresaSmoke.php';

use App\Livewire\Admin\EmpresaUsers\ListEmpresaUser;
use App\Livewire\Admin\EmpresaUsers\SaveEmpresaUser;
use App\Livewire\Empresas\Usuarios\SaveUsuarioEmpresa;
use App\Models\Admin;
use App\Models\PermissionAdmin;
use App\Models\PermissionEmpresa;
use App\Models\UsuarioEmpresa;
use Database\Seeders\GroupSectionPermissionAdminSeeder;
use Database\Seeders\GroupSectionPermissionEmpresaSeeder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Symfony\Component\HttpKernel\Exception\HttpException;

(new GroupSectionPermissionAdminSeeder)->run();
(new GroupSectionPermissionEmpresaSeeder)->run();
$root = Admin::create([
    'name' => 'Root usuarios',
    'username' => 'root-usuarios',
    'email' => 'root-usuarios@test.test',
    'password' => 'ClaveRoot123',
    'level' => Admin::ROOT,
    'status' => Admin::ACTIVO,
]);
auth('admin')->setUser($root);
$permiso = PermissionEmpresa::whereHas('section', function ($query) {
    $query->where('url', 'reservas');
})->where('url', 'list')->firstOrFail();
$urls = PermissionAdmin::whereHas('section', function ($query) {
    $query->where('url', 'empresas.users');
})->pluck('url')->sort()->values()->all();
$check($urls === ['add', 'edit', 'list']);
$check(Route::has('admin.empresas.users.add') && ! Route::has('admin.empresas.users.detail') && ! Route::has('admin.empresas.users.permissions'));

$list = Livewire::test(ListEmpresaUser::class, ['empresa_id' => $empresa->id]);
$check(str_contains($list->html(), route('admin.empresas.users.add', ['empresa_id' => $empresa->id])));
$form = Livewire::test(SaveEmpresaUser::class, ['empresa_id' => $empresa->id]);
$check(str_contains($form->html(), 'name="es_admin"') && str_contains($form->html(), 'Administrador de empresa'));
$check(str_contains(implode('', $form->effects['scripts'] ?? []), 'saveEmpresaUsuarioForm'));
$form->set('nombre', 'Usuario desde Admin')->set('email', 'usuario-desde-admin@test.test')
    ->set('password', 'ClaveSegura123')->set('selectedPermissions', [(string) $permiso->id])->call('save');
$usuario = UsuarioEmpresa::where('email', 'usuario-desde-admin@test.test')->firstOrFail();
$check($usuario->empresa_id === $empresa->id && ! $usuario->isAdmin() && $usuario->hasPermission('reservas', 'list'));
$check(Hash::check('ClaveSegura123', $usuario->password));
$hash = $usuario->password;
$edit = Livewire::test(SaveEmpresaUser::class, ['empresa_id' => $empresa->id, 'usuario_empresa_id' => $usuario->id]);
$check($edit->get('selectedPermissions') === [(string) $permiso->id]);
$edit->set('es_admin', true)->call('save');
$check($usuario->fresh()->isAdmin() && $usuario->fresh()->permisos()->count() === 0 && $usuario->fresh()->password === $hash);
$edit->set('es_admin', false)->set('selectedPermissions', [(string) $permiso->id])->set('estatus', '2')->call('save');
$check(! $usuario->fresh()->isAdmin() && $usuario->fresh()->estatus === 2 && $usuario->fresh()->permisos()->count() === 1);

$ajeno = $usuario->replicate();
$ajeno->empresa_id = $empresaDos->id;
$ajeno->email = 'ajeno-usuarios-admin@test.test';
$ajeno->save();
$reject(function () use ($empresa, $ajeno) {
    (new SaveEmpresaUser)->mount($empresa->id, $ajeno->id);
}, ModelNotFoundException::class);

$permiso->update(['status' => 0]);
$invalid = new SaveEmpresaUser;
$invalid->mount($empresa->id, $usuario->id);
$invalid->nombre = 'No debe persistir';
$invalid->selectedPermissions = [$permiso->id];
$reject(function () use ($invalid) { $invalid->save(); }, ValidationException::class);
$check($usuario->fresh()->nombre === 'Usuario desde Admin');
$permiso->update(['status' => 1]);

$limitado = Admin::create([
    'name' => 'Solo lista',
    'username' => 'solo-lista-usuarios',
    'email' => 'solo-lista-usuarios@test.test',
    'password' => 'ClaveSegura123',
    'level' => Admin::ADMIN,
    'status' => Admin::ACTIVO,
]);
$listPermission = PermissionAdmin::whereHas('section', function ($query) {
    $query->where('url', 'empresas.users');
})->where('url', 'list')->firstOrFail();
$limitado->permissions()->attach($listPermission->id, ['status' => 1]);
auth('admin')->setUser($limitado);
$list = Livewire::test(ListEmpresaUser::class, ['empresa_id' => $empresa->id]);
$check(! str_contains($list->html(), route('admin.empresas.users.add', ['empresa_id' => $empresa->id])));
$reject(function () use ($invalid) { $invalid->save(); }, HttpException::class);

auth('empresa')->setUser($usuarioEmpresa->fresh());
$companyForm = Livewire::test(SaveUsuarioEmpresa::class);
$check(! str_contains($companyForm->html(), 'name="es_admin"'));
$check(str_contains(implode('', $companyForm->effects['scripts'] ?? []), 'saveEmpresaUsuarioForm'));
echo "Usuarios Empresa Admin: alta, switch, permisos, edición, aislamiento, rutas eliminadas y formulario compartido OK\n";
