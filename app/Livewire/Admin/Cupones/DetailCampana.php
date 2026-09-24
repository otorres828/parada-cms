<?php

namespace App\Livewire\Admin\Cupones;

use App\Models\ConfiguracionCupon;
use App\Models\Cupon;
use App\Services\Admin\Access;
use App\Traits\Listing;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.cms')]
class DetailCampana extends Component
{
    use Listing, WithPagination;

    public bool $canViewReservation = false;

    public string $status = '';

    protected array $queryString = [
        'search' => ['except' => ''],
        'status' => ['except' => ''],
        'per_page' => ['except' => 10],
    ];

    #[Locked]
    public ?int $configuracion_cupon_id = null;

    public function mount(?int $configuracion_cupon_id = null): void
    {
        $this->configuracion_cupon_id = $configuracion_cupon_id;
        $this->sortColumn = 'id';
        $this->sortDirection = 'desc';
        Access::authorize('cupones', 'detail');
        $this->canViewReservation = Access::allows('reservas', 'detail');
    }

    public function render()
    {
        $query = Cupon::searchAdmin($this->search, ['configuracion_cupon_id' => $this->configuracion_cupon_id, 'status' => $this->status])->with('reserva');
        $cupones = $this->applySort($query)->paginate($this->per_page);

        return view('livewire.admin.cupones.detail-campana', ['cupones' => $cupones, 'configuracionCupon' => $this->configuracion_cupon_id ? $this->findConfiguracionCupon() : null]);
    }

    public function updated($property): void
    {
        if (in_array($property, ['search', 'status', 'per_page'])) {
            $this->resetPage();
        }
    }

    protected function findConfiguracionCupon(): ConfiguracionCupon
    {
        return ConfiguracionCupon::searchAdmin()->findOrFail($this->configuracion_cupon_id);
    }
}
