<?php

namespace App\Livewire\Admin\EmpresaUsers;

use Livewire\Component;
use App\Models\Empresa;
use App\Models\GroupEmpresa;
use App\Models\PermissionEmpresa;
use App\Models\UsuarioEmpresa;
use App\Services\Admin\Access;
use App\Services\Admin\Audit;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;

#[Layout('layouts.crm')]
class SaveEmpresaUser extends Component
{
    public Collection $groups;

    #[Locked]
    public ?int $usuario_empresa_id = null;

    public string $nombre = '';

    public string $email = '';

    public string $password = '';

    public int|string $estatus = UsuarioEmpresa::ESTADO_ACTIVE;

    public array $selectedPermissions = [];

    public bool $es_admin = false;

    #[Locked]
    public ?int $empresa_id = null;

    public function mount(?int $empresa_id = null, ?int $usuario_empresa_id = null): void
    {
        $this->empresa_id = $empresa_id;
        Empresa::findOrFail($empresa_id);
        $this->usuario_empresa_id = $usuario_empresa_id;
        $this->groups = GroupEmpresa::activeForUserAssignment();

        if ($usuario_empresa_id) {
            $this->editar($this->findUsuarioEmpresa());
        }
    }

    public function render()
    {
        return view('livewire.admin.empresa-users.save-empresa-user');
    }

    public function save()
    {
        Access::authorize('empresas.users', $this->usuario_empresa_id ? 'edit' : 'add');
        $this->validate([
            'nombre' => 'required|string|max:255',
            'email' => ['required', 'email', 'max:255', Rule::unique('usuarios_empresa', 'email')->ignore($this->usuario_empresa_id)],
            'password' => [$this->usuario_empresa_id ? 'nullable' : 'required', 'string', 'min:8', 'max:255'],
            'estatus' => 'required|integer|in:1,2',
            'es_admin' => 'boolean',
            'selectedPermissions' => 'array',
            'selectedPermissions.*' => 'integer|distinct|exists:permissions_empresa,id',
        ], [
            'required' => 'El campo :attribute es obligatorio.',
            'email' => 'Ingresa un correo válido.',
            'unique' => 'Este correo ya está registrado.',
            'min' => 'La contraseña debe tener al menos :min caracteres.',
            'max' => 'El campo :attribute no puede superar :max caracteres.',
            'in' => 'Selecciona un estado válido.',
            'integer' => 'Selecciona una opción válida.',
            'distinct' => 'Hay permisos repetidos.',
            'exists' => 'Hay permisos inválidos.',
            'array' => 'Selecciona permisos válidos.',
        ], [
            'nombre' => 'nombre',
            'email' => 'correo',
            'password' => 'contraseña',
            'estatus' => 'estado',
            'selectedPermissions' => 'permisos',
        ]);

        DB::transaction(function () {
            Access::authorize('empresas.users', $this->usuario_empresa_id ? 'edit' : 'add');
            $usuario = $this->usuario_empresa_id ? $this->findUsuarioEmpresa(true) : new UsuarioEmpresa;

            Empresa::findOrFail($this->empresa_id);
            $ids = $this->es_admin ? [] : PermissionEmpresa::validAssignableIds($this->selectedPermissions, ['usuarios']);
            UsuarioEmpresa::exigir($this->es_admin || count($ids) === count($this->selectedPermissions), 'selectedPermissions', 'Hay permisos inactivos o reservados para el administrador.');

            $usuario->empresa_id = $this->empresa_id;
            $usuario->nombre = $this->nombre;
            $usuario->email = $this->email;
            $usuario->estatus = (int) $this->estatus;

            $usuario->es_admin = (int) $this->es_admin;

            if ($this->password !== '') {
                $usuario->password = $this->password;
                $usuario->remember_token = Str::random(60);
            }

            $usuario->save();

            $usuario->permisos()->sync($ids);
            Audit::record($this->usuario_empresa_id ? 'usuario_empresa.actualizado' : 'usuario_empresa.creado', $usuario, [
                'es_admin' => $usuario->es_admin,
                'permission_ids' => $ids,
            ]);
        });

        session()->flash('admin_usuario_empresa_success', 'Usuario y permisos guardados correctamente.');

        return $this->redirect(route('admin.empresas.users.list', ['empresa_id' => $this->empresa_id]), navigate: true);
    }

    protected function editar(UsuarioEmpresa $usuario): void
    {
        $this->es_admin = $usuario->isAdmin();
        $this->nombre = $usuario->nombre;
        $this->email = $usuario->email;
        $this->estatus = $usuario->estatus;
        $this->selectedPermissions = $usuario->permisos()
            ->whereHas('section', function ($query) {
                $query->where('url', '!=', 'usuarios');
            })->pluck('permissions_empresa.id')->map(function ($id) {
                return (string) $id;
            })->all();
    }

    public function findUsuarioEmpresa(bool $lockForUpdate = false): UsuarioEmpresa
    {
        $query = UsuarioEmpresa::searchAdmin('', [
            'empresa_id' => $this->empresa_id,
        ]);

        if ($lockForUpdate) {
            $query->lockForUpdate();
        }

        return $query->findOrFail($this->usuario_empresa_id);
    }
}
