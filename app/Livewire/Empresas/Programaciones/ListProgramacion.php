<?php

namespace App\Livewire\Empresas\Programaciones;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.crm')]
class ListProgramacion extends Component
{
    public function render(): View
    {
        return view('livewire.empresas.programaciones.list-programacion');
    }
}
