<?php

namespace App\Livewire\Empresas\Viajes;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.crm')]
class ListViaje extends Component
{
    public function render(): View
    {
        return view('livewire.empresas.viajes.list-viaje');
    }
}
