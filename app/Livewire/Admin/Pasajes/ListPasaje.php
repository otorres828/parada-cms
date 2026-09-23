<?php

namespace App\Livewire\Admin\Pasajes;

use App\Models\Empresa;
use App\Exports\PasajesExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Pasaje;
use App\Services\Admin\Access;
use App\Traits\Listing;
use App\Traits\Permissions;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.cms')]
class ListPasaje extends Component
{
    use Listing;
    use Permissions;
    use WithPagination;

    public string $empresa_id = '';

    public string $status = '';

    public string $date_from = '';

    public string $date_to = '';

    public Collection $empresas;

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
        $this->checkPermissions('pasajes',['detail']);
        $this->empresas = Empresa::searchAdmin()->orderBy('nombre')->get();
    }

    public function render()
    {
        $query = Pasaje::searchAdmin($this->search, [
            'empresa_id' => $this->empresa_id,
            'estado_pago' => $this->status,
            'date_from' => $this->date_from,
            'date_to' => $this->date_to
        ]);

        $query = $this->applySort($query);

        $pasajes = $query->paginate($this->per_page);

        return view('livewire.admin.pasajes.list-pasaje', [
            'pasajes' => $pasajes
        ]);
    }

    public function exportExcel()
    {
        Access::authorize('pasajes', 'list');
        Access::authorize('pasajes', 'detail');
        $query = Pasaje::searchAdmin($this->search, [
            'empresa_id' => $this->empresa_id,
            'estado_pago' => $this->status,
            'date_from' => $this->date_from,
            'date_to' => $this->date_to
        ]);

        return Excel::download(new PasajesExport($this->applySort($query)), 'pasajes-'.now()->format('Y-m-d-His').'.xlsx');
    }

    public function updated($property): void
    {
        if (in_array($property, ['empresa_id', 'search', 'status', 'date_from', 'date_to', 'per_page'])) {
            $this->resetPage();
        }
    }
}
