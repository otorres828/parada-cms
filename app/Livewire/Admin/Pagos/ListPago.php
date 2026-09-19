<?php

namespace App\Livewire\Admin\Pagos;

use App\Exports\PagosExport;
use App\Models\Empresa;
use App\Models\Pago;
use App\Services\Admin\Access;
use App\Traits\Listing;
use App\Traits\Permissions;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;

#[Layout('layouts.cms')]
class ListPago extends Component
{
    use Listing;
    use Permissions;
    use WithPagination;

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
        $this->sortColumn = 'id';
        $this->sortDirection = 'desc';
        $this->checkPermissions('pagos');
    }

    public function render()
    {
        $query = $this->paymentQuery();
        $pagos = $query->paginate($this->per_page);
        return view('livewire.admin.pagos.list-pago', [
            'pagos' => $pagos, 'empresas' => Empresa::searchAdmin()->orderBy('nombre')->get(), 
            'capabilities' => Access::capabilities('pagos')]
        );
    }

    public function updated($property): void
    {
        if (in_array($property, ['empresa_id', 'search', 'status', 'date_from', 'date_to', 'per_page'])) {
            $this->resetPage();
        }
    }

    protected function paymentQuery()
    {
        Access::authorize('pagos', 'list');
        $query = Pago::searchAdmin($this->search, ['empresa_id' => $this->empresa_id, 'status' => $this->status, 'date_from' => $this->date_from, 'date_to' => $this->date_to]);
        $query = $this->applySort($query);

        return $query;
    }

    public function export()
    {
        $this->validate(['empresa_id' => 'nullable|integer|exists:empresas,id', 'search' => 'string|max:200', 'status' => 'string|max:30', 'per_page' => 'integer|in:10,25,50,100', 'date_from' => 'nullable|date_format:Y-m-d', 'date_to' => 'nullable|date_format:Y-m-d|after_or_equal:date_from']);

        return Excel::download(new PagosExport($this->paymentQuery()), 'pagos-'.now()->format('Y-m-d-His').'.xlsx');
    }
}
