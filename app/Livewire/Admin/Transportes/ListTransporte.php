<?php

namespace App\Livewire\Admin\Transportes;

use App\Models\Transporte;
use App\Models\Empresa;
use App\Traits\Listing;
use App\Traits\Permissions;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.crm')]
class ListTransporte extends Component
{
    use Listing;
    use Permissions;
    use WithPagination;

    public string $empresa_id = '';

    public string $tipo_transporte = '';

    public string $status = '';

    public Collection $empresas;

    protected array $queryString = [
        'empresa_id' => ['except' => ''],
        'search' => ['except' => ''],
        'per_page' => ['except' => 10],
        'status' => ['except' => ''],
        'tipo_transporte' => ['except' => ''],
    ];

    public function mount(): void
    {
        $this->sortColumn = 'id';
        $this->sortDirection = 'desc';
        $this->checkPermissions('transportes', ['detail']);
        $this->empresas = Empresa::searchAdmin()->orderBy('nombre')->get();
    }

    public function render()
    {
        $query = Transporte::searchAdmin($this->search, [
            'empresa_id' => $this->empresa_id,
            'status' => $this->status,
            'tipo_transporte' => $this->tipo_transporte,
        ]);

        $query = $this->applySort($query);

        $transportes = $query->paginate($this->per_page);

        return view('livewire.admin.transportes.list-transporte', [
            'transportes' => $transportes,
        ]);
    }

    public function updated($property): void
    {
        if (in_array($property, ['empresa_id', 'search', 'status', 'per_page', 'tipo_transporte'])) {
            $this->resetPage();
        }
    }
}
