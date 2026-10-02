<?php

namespace App\Livewire\Empresas\Reservas;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.crm')]
class ListReserva extends Component
{
    public function render(): View
    {
        return view('livewire.empresas.reservas.list-reserva');
    }
}
