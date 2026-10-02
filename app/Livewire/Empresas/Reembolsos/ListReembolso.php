<?php

namespace App\Livewire\Empresas\Reembolsos;

use App\Models\Reembolso;
use App\Traits\Listing;

use App\Traits\TraitGeneral;

use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.crm')]
class ListReembolso extends Component
{
    use Listing;

    use TraitGeneral;
    use WithPagination;

    // Las acciones se habilitarán al adaptar sus pantallas al panel empresarial.
    public bool $canAdd = false;

    public bool $canDetail = false;

    public bool $canReview = false;

    public string $status = '';

    public string $date_from = '';

    public string $date_to = '';

    protected array $queryString = [

        'search' => ['except' => ''],
        'per_page' => ['except' => 10],
        'status' => ['except' => ''],
        'date_from' => ['except' => ''],
        'date_to' => ['except' => ''],
    ];

    public function mount(): void
    {
        $this->date_from = $this->date_from ?: self::getDefaultDesde();
        $this->date_to = $this->date_to ?: self::getDefaultHasta();
        $this->sortColumn = 'id';
        $this->sortDirection = 'desc';

    }

    public function render()
    {
        $query = Reembolso::searchAdmin($this->search, [
            'empresa_id' => auth('empresa')->user()->empresa_id,
            'status' => $this->status,
            'date_from' => $this->date_from,
            'date_to' => $this->date_to,
        ]);

        $query = $this->applySort($query);

        $reembolsos = $query->paginate($this->per_page);

        return view('livewire.empresas.reembolsos.list-reembolso', [
            'reembolsos' => $reembolsos,
        ]);
    }

    public function updated($property): void
    {
        if (in_array($property, ['search', 'status', 'date_from', 'date_to', 'per_page'])) {
            $this->resetPage();
        }
    }
}
