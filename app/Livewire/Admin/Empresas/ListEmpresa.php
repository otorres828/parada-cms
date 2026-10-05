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

#[Layout('layouts.crm')]
class ListEmpresa extends Component
{
    use Listing;
    use Permissions;
    use WithPagination;

    public string $tipo_entidad = '';

    public string $status = '';

    protected array $queryString = [
        'search' => ['except' => ''],
        'per_page' => ['except' => 10],
        'tipo_entidad' => ['except' => ''],
        'status' => ['except' => ''],
    ];

    public function mount(): void
    {
        $this->sortColumn = 'id';
        $this->sortDirection = 'desc';
        $this->checkPermissions('empresas', ['detail']);
    }

    public function render()
    {
        $query = Empresa::searchAdmin($this->search, [
            'tipo_entidad' => $this->tipo_entidad,
            'status' => $this->status,
        ]);

        $query = $this->applySort($query);

        $empresas = $query->paginate($this->per_page);

        return view('livewire.admin.empresas.list-empresa', [
            'empresas' => $empresas,
        ]);
    }

    public function updated($property): void
    {
        if (in_array($property, ['search', 'tipo_entidad', 'status', 'per_page'])) {
            $this->resetPage();
        }
    }

    public function changeStatus(int $id): void
    {
        Access::authorize('empresas', 'edit');

        DB::transaction(function () use ($id) {

            $empresa = Empresa::find($id);

            $inactive = Empresa::ESTADO_DELETE;

            if ((int) $empresa->estatus !== Empresa::ESTADO_ACTIVE && $empresa->estaBloqueadaPorCobranza()) {
                $this->dispatch('admin_empresa_error', message: 'La empresa tiene órdenes de cobro vencidas y no puede activarse.');

                return;
            }

            $empresa->estatus = (int) $empresa->estatus === Empresa::ESTADO_ACTIVE ? $inactive : Empresa::ESTADO_ACTIVE;

            $empresa->save();

            Audit::record('registro.estado', $empresa, ['estatus' => $empresa->estatus]);

        });
        $this->dispatch('admin_empresa_success', message: 'Estado actualizado.');
    }
}
