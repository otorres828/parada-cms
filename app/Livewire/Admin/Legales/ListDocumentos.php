<?php

namespace App\Livewire\Admin\Legales;

use App\Models\Empresa;
use App\Traits\Listing;
use App\Traits\Permissions;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.cms')]
class ListDocumentos extends Component
{
    use Listing, Permissions, WithPagination;

    public string $tipo_entidad = '';

    public string $tipo_contrato = '';

    protected array $queryString = [
        'search' => ['except' => ''],
        'tipo_entidad' => ['except' => ''],
        'tipo_contrato' => ['except' => ''],
        'per_page' => ['except' => 10],
    ];

    public function mount(): void
    {
        $this->checkPermissions('legales', ['detail']);
        $this->sortColumn = 'nombre';
        $this->sortDirection = 'asc';
    }

    public function render()
    {
        $query = Empresa::searchAdmin($this->search, [
            'con_legales' => true,
            'tipo_entidad' => $this->tipo_entidad,
            'tipo_contrato' => $this->tipo_contrato,
        ]);

        $query = $this->applySort($query);

        $empresas = $query->paginate($this->per_page);

        return view('livewire.admin.legales.list-documentos', [
            'empresas' => $empresas,
        ]);
    }

    public function updated($property): void
    {
        if (in_array($property, ['search', 'tipo_entidad', 'tipo_contrato', 'per_page'])) {
            $this->resetPage();
        }
    }
}
