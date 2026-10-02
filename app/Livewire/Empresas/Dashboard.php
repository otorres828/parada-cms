<?php

namespace App\Livewire\Empresas;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.crm')]
#[Title('Resumen de la plataforma')]
class Dashboard extends Component
{
    public function render()
    {
        return view('livewire.empresas.dashboard');
    }
}
