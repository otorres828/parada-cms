<?php

namespace App\Livewire\Admin\EmpresaUsers;

use App\Models\Empresa;
use App\Models\PermissionEmpresa;
use App\Models\UsuarioEmpresa;
use App\Services\Admin\Access;
use App\Services\Admin\Audit;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;

#[Layout('layouts.cms')]
class PermissionEmpresaUser extends Component
{
    #[Locked]
    public ?int $usuario_empresa_id = null;

    #[Locked]
    public ?int $empresa_id = null;

    public array $selectedPermissions = [];

    public Collection $permissions;

    public function mount(?int $empresa_id = null, ?int $usuario_empresa_id = null): void
    {
        $this->empresa_id = $empresa_id;
        Empresa::findOrFail($empresa_id);
        $this->usuario_empresa_id = $usuario_empresa_id;
        $this->permissions = PermissionEmpresa::activeGroupedBySection();
        $usuarioEmpresa = $this->findUsuarioEmpresa();
        $this->selectedPermissions = $usuarioEmpresa->permisos()->pluck('permissions_empresa.id')->map(fn ($id) => (string) $id)->all();
    }

    public function render()
    {
        return view('livewire.admin.empresa-users.permission-empresa-user', ['usuarioEmpresa' => $this->usuario_empresa_id ? $this->findUsuarioEmpresa() : null]);
    }

    public function savePermissions(): void
    {
        Access::authorize('empresas.users', 'permissions');
        $this->validate([
            'selectedPermissions' => 'array',
            'selectedPermissions.*' => 'integer|distinct|exists:permissions_empresa,id',
        ]);
        DB::transaction(function () {
            $usuarioEmpresa = $this->findUsuarioEmpresa();
            $ids = PermissionEmpresa::validAssignableIds($this->selectedPermissions);
            if (count($ids) !== count($this->selectedPermissions)) {
                throw ValidationException::withMessages(['selectedPermissions' => 'Hay permisos inactivos. Actualiza la selección.']);
            }
            $sync = $ids;
            $usuarioEmpresa->permisos()->sync($sync);
            Audit::record('permisos.actualizados', $usuarioEmpresa, ['permission_ids' => $ids]);
        });
        session()->flash('admin_success', 'Permisos actualizados.');
        $this->redirect(route('admin.empresas.users.list', ['empresa_id' => $this->empresa_id]), navigate: true);
    }

    protected function findUsuarioEmpresa(): UsuarioEmpresa
    {
        return UsuarioEmpresa::searchAdmin()->where('empresa_id', $this->empresa_id)->with([])->findOrFail($this->usuario_empresa_id);
    }
}
