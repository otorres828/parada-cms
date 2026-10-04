<?php

namespace App\Livewire\Empresas\Viajes;

use App\Models\Programacion;
use App\Models\TipoCambio;
use App\Models\Viaje;
use App\Traits\Listing;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use App\Livewire\Empresas\EmpresaComponent;
use Livewire\WithPagination;

#[Layout('layouts.crm')]
class DetailViaje extends EmpresaComponent
{
    use Listing;

    #[Locked]
    public ?int $viaje_id = null;

    public bool $canViewPassengers = false;

    public bool $canList = false;

    public Viaje $viaje;

    use WithPagination;

    protected string $paginationTheme = 'bootstrap';

    protected array $queryString = [
        'per_page' => ['except' => 10],
    ];

    public function mount(?int $viaje_id = null): void
    {
        $this->viaje_id = $viaje_id;
        $this->canList = $this->usuarioEmpresa->hasPermission('viajes', 'list');
        $this->viaje = $this->findViaje();
        $this->canViewPassengers = $this->usuarioEmpresa->hasPermission('programaciones', 'detail');
    }

    public function render()
    {

        $this->viaje = $this->findViaje();
        $query = Programacion::searchDetailViajes($this->viaje_id, $this->usuarioEmpresa->empresa_id);

        $programaciones = $query->paginate(max(1, min(100, (int) $this->per_page)));

        return view('livewire.empresas.viajes.detail-viaje', [
            'programaciones' => $programaciones,
            'tipoCambioVigente' => TipoCambio::vigente(),
        ]);
    }

    public function updatedPerPage(): void
    {
        $this->resetPage();
    }

    public function findViaje(): Viaje
    {
        return Viaje::searchAdmin('', ['empresa_id' => $this->usuarioEmpresa->empresa_id])->with([
            'empresa',
            'origenTerminal',
            'destinoTerminal',
            'tramos.origenTerminal',
            'tramos.destinoTerminal',
        ])->findOrFail($this->viaje_id);
    }

}
