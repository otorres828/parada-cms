<?php

namespace App\Livewire\Admin\Legales;

use App\Models\Empresa;
use App\Traits\Listing;
use App\Traits\Permissions;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.cms')]
class ListLegal extends Component
{
    use Listing, Permissions, WithPagination;

    protected array $queryString = [
        'search' => ['except' => ''],
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
        ]);

        $query = $this->applySort($query);

        $empresas = $query->paginate($this->per_page);

        return view('livewire.admin.legales.list-legal', [
            'empresas' => $empresas,
        ]);
    }

    public function updated($property): void
    {
        if (in_array($property, ['search', 'per_page'])) {
            $this->resetPage();
        }
    }
}
