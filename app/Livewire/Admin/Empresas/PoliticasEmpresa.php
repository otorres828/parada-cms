<?php

namespace App\Livewire\Admin\Empresas;

use App\Models\Empresa;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;

#[Layout('layouts.cms')]
class PoliticasEmpresa extends Component
{
    #[Locked]
    public int $empresa_id;

    public function mount(int $empresa_id): void
    {
        $this->empresa_id = $empresa_id;
    }

    public function render()
    {
        return view('livewire.admin.empresas.politicas', [
            'empresa' => Empresa::findOrFail($this->empresa_id),
        ]);
    }
}
