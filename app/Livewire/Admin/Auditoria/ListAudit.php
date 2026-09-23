<?php

namespace App\Livewire\Admin\Auditoria;

use App\Models\Auditoria;
use App\Services\Admin\Access;
use App\Traits\Listing;
use App\Traits\Permissions;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.cms')]
class ListAudit extends Component
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
        $this->checkPermissions('auditoria',['detail']);
    }

    public function render()
    {
        $query = Auditoria::searchAdmin($this->search, [
            'status' => $this->status,
            'date_from' => $this->date_from,
            'date_to' => $this->date_to
        ]);

        $query = $this->applySort($query);

        $auditorias = $query->paginate($this->per_page);

        return view('livewire.admin.auditoria.list-audit', [
            'auditorias' => $auditorias
        ]);
    }

    public function updated($property): void
    {
        if (in_array($property, ['search', 'status', 'date_from', 'date_to', 'per_page'])) {
            $this->resetPage();
        }
    }
}
