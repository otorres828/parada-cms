<?php

namespace App\Livewire\Empresas\Reportes;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.crm')]
class SalesReport extends Component
{
    public function render(): View
    {
        return view('livewire.empresas.reportes.sales-report');
    }
}
