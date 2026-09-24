<?php

namespace App\Livewire\Admin\Viajes;

use App\Models\Empresa;
use App\Models\Viaje;
use App\Traits\Listing;
use App\Traits\Permissions;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.cms')]
class ListViaje extends Component
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
        'status' => ['except' => ''],
    ];

    public function mount(): void
    {
        $this->sortColumn = 'id';
        $this->sortDirection = 'desc';
        $this->checkPermissions('viajes', ['detail']);
        $this->empresas = Empresa::searchAdmin()->orderBy('nombre')->get();
    }

    public function render()
    {
        $query = Viaje::searchAdmin($this->search, [
            'con_tasas' => true,
            'empresa_id' => $this->empresa_id,
            'status' => $this->status,
        ]);

        $query = $this->applySort($query);

        $viajes = $query->paginate($this->per_page);

        return view('livewire.admin.viajes.list-viaje', [
            'viajes' => $viajes,
        ]);
    }

    public function updated($property): void
    {
        if (in_array($property, ['empresa_id', 'search', 'status', 'per_page'])) {
            $this->resetPage();
        }
    }
}
