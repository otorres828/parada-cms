<?php

namespace App\Livewire\Empresas\Reportes;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.crm')]
class RoutesReport extends Component
{
    public function render(): View
    {
        return view('livewire.empresas.reportes.routes-report');
    }
}
