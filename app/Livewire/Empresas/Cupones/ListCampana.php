<?php

namespace App\Livewire\Empresas\Cupones;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.crm')]
class ListCampana extends Component
{
    public function render(): View
    {
        return view('livewire.empresas.cupones.list-campana');
    }
}
