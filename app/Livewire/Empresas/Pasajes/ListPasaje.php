<?php

namespace App\Livewire\Empresas\Pasajes;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.crm')]
class ListPasaje extends Component
{
    public function render(): View
    {
        return view('livewire.empresas.pasajes.list-pasaje');
    }
}
