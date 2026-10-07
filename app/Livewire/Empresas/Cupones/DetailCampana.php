<?php

namespace App\Livewire\Empresas\Cupones;

use App\Livewire\Empresas\EmpresaComponent;
use App\Models\ConfiguracionCupon;
use App\Models\Cupon;
use App\Services\Empresa\Access;
use App\Traits\Listing;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\WithPagination;

#[Layout('layouts.crm')]
class DetailCampana extends EmpresaComponent
{
    use Listing;
    use WithPagination;

    public bool $canViewReservation = false;

    public string $status = '';

    #[Locked]
    public ?int $configuracion_cupon_id = null;

    protected array $queryString = [
        'search' => ['except' => ''],
        'status' => ['except' => ''],
        'per_page' => ['except' => 10],
    ];

    public function mount(?int $configuracion_cupon_id = null): void
    {
        $this->configuracion_cupon_id = $configuracion_cupon_id;
        $this->findConfiguracionCupon();
        $this->sortColumn = 'id';
        $this->sortDirection = 'desc';
        $this->canViewReservation = Access::allows('reservas', 'detail');
    }

    public function render()
    {
        $configuracionCupon = $this->findConfiguracionCupon();
        
        $query = Cupon::searchAdmin($this->search, [
            'configuracion_cupon_id' => $configuracionCupon->id,
            'status' => $this->status,
        ])->with(['reserva' => function ($query) {
            $query->whereHas('programacion.viaje', function ($query) {
                $query->where('empresa_id', $this->usuarioEmpresa->empresa_id);
            });
        }]);

        return view('livewire.empresas.cupones.detail-campana', [
            'configuracionCupon' => $configuracionCupon,
            'cupones' => $this->applySort($query)->paginate(max(1, min(100, (int) $this->per_page))),
        ]);
    }

    public function updated(string $property): void
    {
        if (in_array($property, ['search', 'status', 'per_page'], true)) {
            $this->resetPage();
        }
    }

    public function findConfiguracionCupon(): ConfiguracionCupon
    {
        return ConfiguracionCupon::searchAdmin('', [
            'empresa_id' => $this->usuarioEmpresa->empresa_id,
        ])->with('empresa')->withCount('cupones')->findOrFail($this->configuracion_cupon_id);
    }
}
