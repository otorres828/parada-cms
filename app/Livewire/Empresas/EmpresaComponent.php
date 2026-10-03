<?php

namespace App\Livewire\Empresas;

use App\Models\UsuarioEmpresa;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Locked;
use Livewire\Component;

abstract class EmpresaComponent extends Component
{
    protected UsuarioEmpresa $usuarioEmpresa;

    #[Locked]
    public bool $viewTasaServicio = false;

    public function boot(): void
    {
        $this->usuarioEmpresa = Auth::guard('empresa')->user();
        $this->viewTasaServicio = $this->usuarioEmpresa->empresa->viewTasaServicio();
    }
}