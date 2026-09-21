<?php

namespace App\Livewire\Admin\Campanas;

use App\Models\ConfiguracionCupon;
use App\Services\Admin\Access;
use App\Services\Admin\Audit;
use App\Traits\Listing;
use App\Traits\Permissions;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.cms')]
class ListCampana extends Component
{
    use Listing;
    use Permissions;
    use WithPagination;

    public string $status = '';

    public string $date_from = '';

    public string $date_to = '';

    protected array $queryString = [
        'search' => ['except' => ''], 
        'per_page' => ['except' => 10], 
        'status' => ['except' => ''], 
        'date_from' => ['except' => ''], 
        'date_to' => ['except' => '']
    ];

    public function mount(): void
    {
        $this->sortColumn = 'id';
        $this->sortDirection = 'desc';
        $this->checkPermissions('campanas');
    }

    public function render()
    {
        $query = ConfiguracionCupon::searchAdmin($this->search, ['status' => $this->status, 'date_from' => $this->date_from, 'date_to' => $this->date_to]);
        $query = $this->applySort($query);
        $campanas = $query->paginate($this->per_page);
        return view('livewire.admin.campanas.list-campana', ['campanas' => $campanas, 'capabilities' => Access::capabilities('campanas')]);
    }

    public function updated($property): void
    {
        if (in_array($property, ['search', 'status', 'date_from', 'date_to', 'per_page'])) {
            $this->resetPage();
        }
    }

    public function changeStatus(int $id): void
    {
        Access::authorize('campanas', 'edit');

        DB::transaction(function () use ($id) {

            $query = ConfiguracionCupon::searchAdmin();

            $configuracionCupon = $query->whereKey($id)->lockForUpdate()->firstOrFail();

            $inactive = 2;

            $configuracionCupon->estatus = (int) $configuracionCupon->estatus === 1 ? $inactive : 1;

            $configuracionCupon->save();

            Audit::record('registro.estado', $configuracionCupon, ['estatus' => $configuracionCupon->estatus]);

        });
        
        $this->dispatch('successEventList', message: 'Estado actualizado.');
    }
}
