<?php

namespace App\Livewire\Admin\Viajes;

use App\Models\Programacion;
use App\Models\Viaje;
use App\Services\Admin\Access;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;

#[Layout('layouts.cms')]
class DetailViaje extends Component
{
    #[Locked]
    public ?int $viaje_id = null;

    public $canViewPassengers= false;

    public Viaje $viaje;

    use WithPagination;

    public int $per_page = 10;

    protected string $paginationTheme = 'bootstrap';

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
        return view('livewire.admin.viajes.detail-viaje', ['programaciones' => Programacion::searchDetailViajes($this->viaje_id)->paginate(max(1, min(100, $this->per_page)))]);
    }


    public function updatedPerPage(): void
    {
        $this->resetPage();
    }
}
