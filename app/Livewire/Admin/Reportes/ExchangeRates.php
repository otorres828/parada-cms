<?php

namespace App\Livewire\Admin\Reportes;

use App\Models\TipoCambio;
use App\Services\Admin\Access;
use App\Traits\Listing;
use Illuminate\Support\Facades\Artisan;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.cms')]
class ExchangeRates extends Component
{
    use WithPagination, Listing;

    protected string $paginationTheme = 'bootstrap';

    public bool $canUpdate = false;

    protected array $queryString = [
        'per_page' => ['except' => 10],
    ];
    
    public function mount(): void
    {
        $this->canUpdate = Access::allows('reportes', 'update-exchange-rates');
    }

    public function render()
    {
        return view('livewire.admin.reportes.exchange-rates', [
            'rows' => TipoCambio::query()
                ->latest('timestamp')
                ->latest('id')
                ->paginate($this->per_page),
        ]);
    }

    public function updateRates(): void
    {
        Access::authorize('reportes', 'update-exchange-rates');

        $exitCode = Artisan::call('tipos-cambio:actualizar');

        if ($exitCode !== 0) {
            $this->dispatch('errorEventList', message: trim(Artisan::output()) ?: 'No fue posible actualizar las tasas de cambio.');

            return;
        }

        $this->resetPage();
        $this->dispatch('successEventList', message: 'Las tasas de cambio fueron actualizadas correctamente.');
    }
}
