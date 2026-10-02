<?php

namespace App\Livewire\Empresas\Transportes;

use App\Models\Transporte;
use App\Traits\Listing;
use App\Traits\PermissionsEmpresa;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.crm')]
class ListTransporte extends Component
{
    use Listing;
    use PermissionsEmpresa;
    use WithPagination;

    public string $status = '';

    protected array $queryString = [
        'search' => ['except' => ''],
        'per_page' => ['except' => 10],
        'status' => ['except' => ''],
    ];

    public function mount(): void
    {
        $this->sortColumn = 'id';
        $this->sortDirection = 'desc';
        $this->checkPermissions('transportes', ['detail']);
    }

    public function render()
    {
        $query = Transporte::searchAdmin($this->search, [
            'empresa_id' => auth('empresa')->user()->empresa_id,
            'status' => $this->status,
            'tipo_transporte' => auth('empresa')->user()->empresa->getTipoTransporte(),
        ]);

        $query = $this->applySort($query);

        $transportes = $query->paginate(max(1, min(100, (int) $this->per_page)));

        return view('livewire.empresas.transportes.list-transporte', [
            'transportes' => $transportes,
        ]);
    }

    public function updated(string $property): void
    {
        if (in_array($property, ['search', 'status', 'per_page'])) {
            $this->resetPage();
        }
    }
}
