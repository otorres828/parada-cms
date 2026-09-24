<?php

namespace App\Livewire\Admin\Empresas;

use App\Models\Empresa;
use App\Services\Admin\Access;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;

#[Layout('layouts.cms')]
class DetailEmpresa extends Component
{
    #[Locked]
    public ?int $empresa_id = null;

    public bool $canListUser = false;

    public Empresa $empresa;

    public function mount(?int $empresa_id = null): void
    {
        $this->empresa_id = $empresa_id;
        $this->canListUser = Access::allows('empresas.users', 'list');
        $this->empresa = $this->findEmpresa();
    }

    public function render()
    {
        return view('livewire.admin.empresas.detail-empresa');
    }

    protected function findEmpresa(): Empresa
    {
        return Empresa::findAdminDetail($this->empresa_id);
    }
}
