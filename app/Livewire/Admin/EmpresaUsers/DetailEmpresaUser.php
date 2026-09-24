<?php

namespace App\Livewire\Admin\EmpresaUsers;

use App\Models\Empresa;
use App\Models\UsuarioEmpresa;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;

#[Layout('layouts.cms')]
class DetailEmpresaUser extends Component
{
    #[Locked]
    public ?int $usuario_empresa_id = null;

    #[Locked]
    public ?int $empresa_id = null;

    public function mount(?int $empresa_id = null, ?int $usuario_empresa_id = null): void
    {
        $this->empresa_id = $empresa_id;
        Empresa::findOrFail($empresa_id);
        $this->usuario_empresa_id = $usuario_empresa_id;
        $usuarioEmpresa = $this->findUsuarioEmpresa();
    }

    public function render()
    {
        return view('livewire.admin.empresa-users.detail-empresa-user', ['usuarioEmpresa' => $this->usuario_empresa_id ? $this->findUsuarioEmpresa() : null]);
    }

    protected function findUsuarioEmpresa(): UsuarioEmpresa
    {
        return UsuarioEmpresa::searchAdmin()->where('empresa_id', $this->empresa_id)->with([])->findOrFail($this->usuario_empresa_id);
    }
}
