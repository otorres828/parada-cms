<?php

namespace App\Livewire\Empresas\Reprogramaciones;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.crm')]
class ListReprogramacion extends Component
{
    public function render(): View
    {
        return view('livewire.empresas.reprogramaciones.list-reprogramacion');
    }
}
