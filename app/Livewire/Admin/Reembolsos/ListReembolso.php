<?php

namespace App\Livewire\Admin\Reembolsos;

use App\Models\Empresa;
use App\Models\Reembolso;
use App\Traits\Listing;
use App\Traits\Permissions;
use App\Traits\TraitGeneral;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.cms')]
class ListReembolso extends Component
{
    use Listing;
    use Permissions;
    use TraitGeneral;
    use WithPagination;

    public Collection $empresas;

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
        $this->date_from = $this->date_from ?: self::getDefaultDesde();
        $this->date_to = $this->date_to ?: self::getDefaultHasta();
        $this->sortColumn = 'id';
        $this->sortDirection = 'desc';
        $this->checkPermissions('reembolsos',['detail', 'review']);
        $this->empresas = Empresa::searchAdmin()->orderBy('nombre')->get();
    }

    public function render()
    {
        $query = Reembolso::searchAdmin($this->search, [
            'empresa_id' => $this->empresa_id,
            'status' => $this->status,
            'date_from' => $this->date_from,
            'date_to' => $this->date_to
        ]);

        $query = $this->applySort($query);

        $reembolsos = $query->paginate($this->per_page);

        return view('livewire.admin.reembolsos.list-reembolso', [
            'reembolsos' => $reembolsos
        ]);
    }

    public function updated($property): void
    {
        if (in_array($property, ['empresa_id', 'search', 'status', 'date_from', 'date_to', 'per_page'])) {
            $this->resetPage();
        }
    }
}

