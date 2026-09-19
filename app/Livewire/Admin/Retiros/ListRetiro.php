<?php

namespace App\Livewire\Admin\Retiros;

use App\Models\Empresa;
use App\Models\Retiro;
use App\Services\Admin\Access;
use App\Traits\Listing;
use App\Traits\Permissions;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.cms')]
class ListRetiro extends Component
{
    use Listing;
    use Permissions;
    use WithPagination;

    public string $empresa_id = '';

    public string $status = '';

    public string $date_from = '';

    public string $date_to = '';

    protected array $queryString = [
        'empresa_id' => ['except' => ''], 
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
        $this->checkPermissions('retiros');
    }

    public function render()
    {
        $query = Retiro::searchAdmin($this->search, ['empresa_id' => $this->empresa_id, 'status' => $this->status, 'date_from' => $this->date_from, 'date_to' => $this->date_to]);
        $query = $this->applySort($query);
        $retiros = $query->paginate($this->per_page);
        return view('livewire.admin.retiros.list-retiro', ['retiros' => $retiros, 'empresas' => Empresa::searchAdmin()->orderBy('nombre')->get(), 'capabilities' => Access::capabilities('retiros')]);
    }

    public function updated($property): void
    {
        if (in_array($property, ['empresa_id', 'search', 'status', 'date_from', 'date_to', 'per_page'])) {
            $this->resetPage();
        }
    }
}
