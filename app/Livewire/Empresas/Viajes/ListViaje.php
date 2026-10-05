<?php

namespace App\Livewire\Empresas\Viajes;

use App\Livewire\Empresas\EmpresaComponent;
use App\Models\Viaje;
use App\Traits\Listing;
use App\Traits\PermissionsEmpresa;
use Illuminate\Support\Facades\DB;
use App\Services\Empresa\Access;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;

#[Layout('layouts.crm')]
class ListViaje extends EmpresaComponent
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
        $this->checkPermissions('viajes', ['detail']);
    }

    public function render()
    {
        $query = Viaje::searchAdmin($this->search, [
            'con_tasas' => true,
            'empresa_id' => $this->usuarioEmpresa->empresa_id,
            'status' => $this->status,
        ]);

        $query = $this->applySort($query);

        $viajes = $query->paginate(max(1, min(100, (int) $this->per_page)));

        return view('livewire.empresas.viajes.list-viaje', [
            'viajes' => $viajes,
        ]);
    }

    public function changeStatus(int $id): void
    {
        Access::authorize('viajes', 'edit');

        DB::transaction(function () use ($id) {

            $registro = Viaje::searchAdmin('', [
                'empresa_id' => $this->usuarioEmpresa->empresa_id,
            ])->whereKey($id)->lockForUpdate()->firstOrFail();

            $registro->estatus = $registro->estatus === Viaje::ESTADO_ACTIVE
                ? Viaje::ESTADO_INACTIVE
                : Viaje::ESTADO_ACTIVE;

            $registro->save();

        });

        $this->dispatch('empresas_viaje_success', message: 'Estado actualizado.');
    }

    public function updated(string $property): void
    {
        if (in_array($property, ['search', 'status', 'per_page'])) {
            $this->resetPage();
        }
    }
}
