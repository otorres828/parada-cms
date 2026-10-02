<?php

namespace App\Livewire\Empresas;

use App\Models\UsuarioEmpresa;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

abstract class EmpresaComponent extends Component
{
    protected UsuarioEmpresa $usuarioEmpresa;

    public function boot(): void
    {
        $this->usuarioEmpresa = Auth::guard('empresa')->user();
    }
}