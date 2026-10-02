<?php

namespace App\Livewire\Empresas\Usuarios;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.crm')]
class ListUsuario extends Component
{
    public function render(): View
    {
        return view('livewire.empresas.usuarios.list-usuario');
    }
}
