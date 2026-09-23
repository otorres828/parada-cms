<?php

namespace App\Livewire\Admin\Autobuses;

use App\Models\Autobus;
use App\Models\Empresa;
use App\Services\Admin\Access;
use App\Traits\Listing;
use App\Traits\Permissions;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.cms')]
class ListAutobus extends Component
{
    use Listing;
    use Permissions;
    use WithPagination;

    public string $empresa_id = '';

    public string $status = '';

    public Collection $empresas;

    protected array $queryString = [
        'empresa_id' => ['except' => ''], 
        'search' => ['except' => ''], 
        'per_page' => ['except' => 10], 
        'status' => ['except' => '']
    ];

    public function mount(): void
    {
        $this->sortColumn = 'id';
        $this->sortDirection = 'desc';
        $this->checkPermissions('autobuses',['detail']);
        $this->empresas = Empresa::searchAdmin()->orderBy('nombre')->get();
    }

    public function render()
    {
        $query = Autobus::searchAdmin($this->search, [
            'empresa_id' => $this->empresa_id,
            'status' => $this->status
        ]);

        $query = $this->applySort($query);

        $autobuses = $query->paginate($this->per_page);

        return view('livewire.admin.autobuses.list-autobus', [
            'autobuses' => $autobuses
        ]);
    }

    public function updated($property): void
    {
        if (in_array($property, ['empresa_id', 'search', 'status', 'per_page'])) {
            $this->resetPage();
        }
    }
}
