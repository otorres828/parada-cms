<?php

namespace App\Livewire\Admin\EmpresaUsers;

use App\Models\Empresa;
use App\Models\UsuarioEmpresa;
use App\Services\Admin\Access;
use App\Services\Admin\Audit;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;

#[Layout('layouts.cms')]
class SaveEmpresaUser extends Component
{
    public UsuarioEmpresa $usuarioEmpresa;

    #[Locked]
    public ?int $empresa_id = null;

    #[Locked]
    public ?int $usuario_empresa_id = null;

    public string $nombre = '';

    public string $email = '';

    public string $password = '';

    public $es_admin = 0;

    public $estatus = 1;

    public function mount(?int $empresa_id = null, ?int $usuario_empresa_id = null): void
    {
        $this->empresa_id = $empresa_id;
        $this->usuario_empresa_id = $usuario_empresa_id;
        Access::authorize('empresas.users', $usuario_empresa_id ? 'edit' : 'add');
        Empresa::findOrFail($empresa_id);
        if ($usuario_empresa_id) {
            $this->editar(UsuarioEmpresa::searchAdmin('', ['empresa_id' => $empresa_id])->findOrFail($usuario_empresa_id));
        }
    }

    public function render()
    {
        return view('livewire.admin.empresa-users.save-empresa-user');
    }

    public function save()
    {
        Access::authorize('empresas.users', $this->usuario_empresa_id ? 'edit' : 'add');
        $this->validate(['nombre' => 'required|string|max:255', 'email' => ['required', 'email', Rule::unique('usuarios_empresa', 'email')->ignore($this->usuario_empresa_id)], 'password' => [$this->usuario_empresa_id ? 'nullable' : 'required', 'string', 'min:10', 'max:255'], 'es_admin' => 'required|boolean', 'estatus' => 'required|boolean']);
        DB::transaction(function () {
            Empresa::findOrFail($this->empresa_id);
            $usuario = $this->usuario_empresa_id ? UsuarioEmpresa::searchAdmin('', ['empresa_id' => $this->empresa_id])->lockForUpdate()->findOrFail($this->usuario_empresa_id) : new UsuarioEmpresa;
            $usuario->empresa_id = $this->empresa_id;
            $usuario->nombre = $this->nombre;
            $usuario->email = $this->email;
            $usuario->es_admin = $this->es_admin;
            $usuario->estatus = $this->estatus;
            if ($this->password !== '') {
                $usuario->password = $this->password;
            }
            $usuario->save();
            Audit::record('usuario_empresa.guardado', $usuario);
        });
        session()->flash('admin_success', 'Usuario de empresa guardado.');

        return $this->redirect(route('admin.empresas.users.list', ['empresa_id' => $this->empresa_id]), navigate: true);
    }

    protected function editar(UsuarioEmpresa $usuario): void
    {
        $this->usuarioEmpresa = $usuario;
        $this->nombre = $usuario->nombre;
        $this->email = $usuario->email;
        $this->es_admin = (int) $usuario->es_admin;
        $this->estatus = (int) $usuario->estatus;
    }
}
