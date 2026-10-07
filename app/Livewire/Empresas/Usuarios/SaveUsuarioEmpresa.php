<?php

namespace App\Livewire\Empresas\Usuarios;

use App\Livewire\Empresas\EmpresaComponent;
use App\Models\GroupEmpresa;
use App\Models\PermissionEmpresa;
use App\Models\UsuarioEmpresa;
use App\Services\Empresa\Access;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;

#[Layout('layouts.crm')]
class SaveUsuarioEmpresa extends EmpresaComponent
{
    public Collection $groups;

    #[Locked]
    public ?int $usuario_empresa_id = null;

    public string $nombre = '';

    public string $email = '';

    public string $password = '';

    public int|string $estatus = UsuarioEmpresa::ESTADO_ACTIVE;

    public array $selectedPermissions = [];

    #[Locked]
    public bool $editingAdmin = false;

    public function mount(?int $usuario_empresa_id = null): void
    {
        $this->usuario_empresa_id = $usuario_empresa_id;
        $this->groups = GroupEmpresa::activeForUserAssignment();

        if ($usuario_empresa_id) {
            $this->editar($this->findUsuarioEmpresa());
        }
    }

    public function render()
    {
        return view('livewire.empresas.usuarios.save-usuario-empresa');
    }

    public function save()
    {
        Access::authorize('usuarios', $this->usuario_empresa_id ? 'edit' : 'add');
        $this->validate([
            'nombre' => 'required|string|max:255',
            'email' => ['required', 'email', 'max:255', Rule::unique('usuarios_empresa', 'email')->ignore($this->usuario_empresa_id)],
            'password' => [$this->usuario_empresa_id ? 'nullable' : 'required', 'string', 'min:10', 'max:255'],
            'estatus' => 'required|integer|in:1,2',
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
            Access::authorize('usuarios', $this->usuario_empresa_id ? 'edit' : 'add');
            $usuario = $this->usuario_empresa_id ? $this->findUsuarioEmpresa(true) : new UsuarioEmpresa;

            UsuarioEmpresa::exigir($usuario->id !== $this->usuarioEmpresa->id || (int) $this->estatus === UsuarioEmpresa::ESTADO_ACTIVE, 'estatus', 'No puedes desactivar tu propia cuenta.');
            $ids = $usuario->isAdmin() ? [] : PermissionEmpresa::validAssignableIds($this->selectedPermissions, ['usuarios']);
            UsuarioEmpresa::exigir($usuario->isAdmin() || count($ids) === count($this->selectedPermissions), 'selectedPermissions', 'Hay permisos inactivos o reservados para el administrador.');

            $usuario->empresa_id = $this->usuarioEmpresa->empresa_id;
            $usuario->nombre = $this->nombre;
            $usuario->email = $this->email;
            $usuario->estatus = (int) $this->estatus;

            if (! $usuario->exists) {
                $usuario->es_admin = 0;
            }

            if ($this->password !== '') {
                $usuario->password = $this->password;
                $usuario->remember_token = Str::random(60);
            }

            $usuario->save();

            if (! $usuario->isAdmin()) {
                $usuario->permisos()->sync($ids);
            }
        });

        session()->flash('empresas_usuario_success', 'Usuario y permisos guardados correctamente.');

        return $this->redirect(route('empresas.usuarios.list'), navigate: true);
    }

    public function boot(): void
    {
        parent::boot();
        Access::authorize('usuarios', $this->usuario_empresa_id ? 'edit' : 'add');
    }

    protected function editar(UsuarioEmpresa $usuario): void
    {
        $this->editingAdmin = $usuario->isAdmin();
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
            'empresa_id' => $this->usuarioEmpresa->empresa_id,
        ]);

        if ($lockForUpdate) {
            $query->lockForUpdate();
        }

        return $query->findOrFail($this->usuario_empresa_id);
    }
}
