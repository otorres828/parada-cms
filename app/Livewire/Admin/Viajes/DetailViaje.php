<?php

namespace App\Livewire\Admin\Viajes;

use App\Models\Programacion;
use App\Models\TipoCambio;
use App\Models\Viaje;
use App\Services\Admin\Access;
use App\Traits\Listing;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.crm')]
class DetailViaje extends Component
{
    use Listing;

    #[Locked]
    public ?int $viaje_id = null;

    public bool $canViewPassengers = false;

    public Viaje $viaje;

    use WithPagination;

    protected string $paginationTheme = 'bootstrap';

    protected array $queryString = [
        'per_page' => ['except' => 10],
    ];

    public function mount(?int $viaje_id = null): void
    {
        $this->viaje_id = $viaje_id;
        $this->viaje = $this->findViaje();
        $this->canViewPassengers = Access::allows('programaciones', 'detail');
    }

    public function findViaje(): Viaje
    {
        return Viaje::searchAdmin()->with([
            'empresa',
            'origenTerminal',
            'destinoTerminal',
            'tramos.origenTerminal',
            'tramos.destinoTerminal',
        ])->findOrFail($this->viaje_id);
    }

    public function render()
    {

        $query = Programacion::searchDetailViajes($this->viaje_id);

        $programaciones = $query->paginate($this->per_page);

        return view('livewire.admin.viajes.detail-viaje', [
            'programaciones' => $programaciones,
            'tipoCambioVigente' => TipoCambio::vigente(),
        ]);
    }

    public function updatedPerPage(): void
    {
        $this->resetPage();
    }
}
