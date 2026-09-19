<?php

namespace App\Livewire\Admin\Empresas;

use App\Models\Empresa;
use App\Services\Admin\Access;
use App\Services\Admin\Finance;
use App\Traits\Permissions;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;

#[Layout('layouts.cms')]
class DetailEmpresa extends Component
{
    use Permissions;

    #[Locked]
    public ?int $empresa_id = null;

    public bool $canListUser = false;

    public Empresa $empresa;

    public array $balance = [
        'total' => 0,
        'total_pagado' => 0,
        'total_pendiente' => 0,
    ];

    public function mount(?int $empresa_id = null): void
    {
        $this->empresa_id = $empresa_id;
        Access::authorize('empresas', 'detail');
        $this->balance = Finance::balance($empresa_id);
        $this->canListUser = Access::allows('empresas.users', 'list');
        $this->empresa = $this->findEmpresa();
    }

    public function render()
    {
        return view('livewire.admin.empresas.detail-empresa');
    }

    protected function findEmpresa(): Empresa
    {
        return Empresa::findOrFail($this->empresa_id);
    }
}
