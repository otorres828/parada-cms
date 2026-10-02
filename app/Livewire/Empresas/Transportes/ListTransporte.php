<?php

namespace App\Livewire\Empresas\Transportes;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.crm')]
class ListTransporte extends Component
{
    public function render(): View
    {
        return view('livewire.empresas.transportes.list-transporte');
    }
}
