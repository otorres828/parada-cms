<?php

namespace App\Livewire\Admin\Viajes;

use App\Models\Programacion;
use App\Models\Viaje;
use App\Services\Admin\Access;
use App\Traits\Listing;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;

#[Layout('layouts.cms')]
class DetailViaje extends Component
{
    use Listing;

    #[Locked]
    public ?int $viaje_id = null;

    public $canViewPassengers= false;

    public Viaje $viaje;

    use WithPagination;

    protected string $paginationTheme = 'bootstrap';

    
    protected array $queryString = [
        'per_page' => ['except' => 10],
    ];

    public function mount(?int $viaje_id = null): void
    {
        $this->viaje_id = $viaje_id;
        Access::authorize('viajes', 'detail');
        $viaje = Viaje::searchAdmin()->with([
            'empresa',
            'origenTerminal',
            'destinoTerminal',
            'tramos.origenTerminal',
            'tramos.destinoTerminal',
            'programaciones.tramoPrecios.origenTerminal',
            'programaciones.tramoPrecios.destinoTerminal',
        ])->find($viaje_id);
        if (!$viaje) {
            abort(404);
        }
        $this->viaje = $viaje;
        $this->canViewPassengers = Access::allows('programaciones', 'passengers');
    }

    public function render()
    {

        $query = Programacion::searchDetailViajes($this->viaje_id);

        $programaciones = $query->paginate($this->per_page);

        return view('livewire.admin.viajes.detail-viaje', [
            'programaciones' => $programaciones
        ]);
    }


    public function updatedPerPage(): void
    {
        $this->resetPage();
    }
}
