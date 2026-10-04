<?php

namespace App\Livewire\Empresas\Transportes;

use App\Livewire\Empresas\EmpresaComponent;
use App\Models\Transporte;
use App\Services\Empresa\Access;
use Illuminate\Support\Facades\DB;
use App\Traits\Listing;
use App\Traits\PermissionsEmpresa;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;

#[Layout('layouts.crm')]
class ListTransporte extends EmpresaComponent
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
            'empresa_id' => $this->usuarioEmpresa->empresa_id,
            'status' => $this->status,
            'tipo_transporte' => $this->usuarioEmpresa->empresa->getTipoTransporte(),
        ]);

        $query = $this->applySort($query);

        $transportes = $query->paginate(max(1, min(100, (int) $this->per_page)));

        return view('livewire.empresas.transportes.list-transporte', [
            'transportes' => $transportes,
        ]);
    }

    public function changeStatus(int $id): void
    {
        Access::authorize('transportes', 'edit');

        DB::transaction(function () use ($id) {
            $transporte = Transporte::searchAdmin('', [
                'empresa_id' => $this->usuarioEmpresa->empresa_id,
                'tipo_transporte' => $this->usuarioEmpresa->empresa->getTipoTransporte(),
            ])->where('es_plantilla', false)->whereKey($id)->lockForUpdate()->firstOrFail();
            $transporte->estatus = $transporte->estatus === Transporte::ESTADO_ACTIVE
                ? Transporte::ESTADO_INACTIVE
                : Transporte::ESTADO_ACTIVE;
            $transporte->save();
        });

        $this->dispatch('successEventList', message: 'Estado actualizado.');
    }

    public function updated(string $property): void
    {
        if (in_array($property, ['search', 'status', 'per_page'])) {
            $this->resetPage();
        }
    }
}
