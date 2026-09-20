<?php

namespace App\Livewire\Admin\Programaciones;

use App\Models\Empresa;
use App\Models\Programacion;
use App\Services\Admin\Access;
use App\Traits\Listing;
use App\Traits\Permissions;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.cms')]
class ListProgramacion extends Component
{
    use Listing;
    use Permissions;
    use WithPagination;

    public string $empresa_id = '';

    public string $status = '';

    public string $date_from = '';

    public string $date_to = '';

    public Collection $empresas;

    public bool $canViewPassengers = false;

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
        $this->checkPermissions('programaciones');
        $this->empresas = Empresa::searchAdmin()->orderBy('nombre')->get();
        $this->canViewPassengers = Access::allows('programaciones', 'passengers');
    }

    public function render()
    {
        $query = Programacion::searchAdmin($this->search, [
            'empresa_id' => $this->empresa_id, 
            'status' => $this->status, 
            'date_from' => $this->date_from, 
            'date_to' => $this->date_to
        ]);

        $query = $this->applySort($query);

        $programaciones = $query->paginate($this->per_page);

        info($programaciones->toArray());
        return view('livewire.admin.programaciones.list-programacion', [
            'programaciones' => $programaciones, 
        ]);
    }

    public function updated($property): void
    {
        if (in_array($property, ['empresa_id', 'search', 'status', 'date_from', 'date_to', 'per_page'])) {
            $this->resetPage();
        }
    }
}
