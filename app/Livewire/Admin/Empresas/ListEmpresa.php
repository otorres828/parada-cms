<?php

namespace App\Livewire\Admin\Empresas;

use App\Models\Empresa;
use App\Services\Admin\Access;
use App\Services\Admin\Audit;
use App\Traits\Listing;
use App\Traits\Permissions;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.cms')]
class ListEmpresa extends Component
{
    use Listing;
    use Permissions;
    use WithPagination;

    public string $status = '';

    protected array $queryString = [
        'search' => ['except' => ''], 
        'per_page' => ['except' => 10], 
        'status' => ['except' => '']
    ];

    public function mount(): void
    {
        $this->sortColumn = 'id';
        $this->sortDirection = 'desc';
        $this->checkPermissions('empresas',['detail']);
    }

    public function render()
    {
        $query = Empresa::searchAdmin($this->search, ['status' => $this->status]);

        $query = $this->applySort($query);

        $empresas = $query->paginate($this->per_page);

        return view('livewire.admin.empresas.list-empresa', [
            'empresas' => $empresas, 
        ]);
    }

    public function updated($property): void
    {
        if (in_array($property, ['search', 'status', 'per_page'])) {
            $this->resetPage();
        }
    }

    public function changeStatus(int $id): void
    {
        Access::authorize('empresas', 'edit');
        
        DB::transaction(function () use ($id) {
            
            $empresa = Empresa::find($id);
            
            $inactive = Empresa::ESTADO_INACTIVE;
            
            $empresa->estatus = (int) $empresa->estatus === Empresa::ESTADO_ACTIVE ? $inactive : Empresa::ESTADO_ACTIVE;
            
            $empresa->save();
            
            Audit::record('registro.estado', $empresa, ['estatus' => $empresa->estatus]);
            
        });
        $this->dispatch('successEventList', message: 'Estado actualizado.');
    }
}
