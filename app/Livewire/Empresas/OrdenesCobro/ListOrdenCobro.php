<?php

namespace App\Livewire\Empresas\OrdenesCobro;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.crm')]
class ListOrdenCobro extends Component
{
    public function render(): View
    {
        return view('livewire.empresas.ordenes-cobro.list-orden-cobro');
    }
}
