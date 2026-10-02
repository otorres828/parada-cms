<?php

namespace App\Livewire\Empresas\ValidacionPagos;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.crm')]
class ListValidacionPago extends Component
{
    public function render(): View
    {
        return view('livewire.empresas.validacion-pagos.list-validacion-pago');
    }
}
