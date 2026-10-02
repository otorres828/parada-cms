<?php

namespace App\Livewire\Empresas\PoliticasEmbarque;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.crm')]
class SavePoliticaEmbarque extends Component
{
    public function render(): View
    {
        return view('livewire.empresas.politicas-embarque.save-politica-embarque');
    }
}
