<?php

namespace App\Livewire\Empresas\Cupones;

use App\Livewire\Empresas\EmpresaComponent;
use App\Models\ConfiguracionCupon;
use App\Services\Empresa\Access;
use App\Traits\Listing;
use App\Traits\PermissionsEmpresa;
use App\Traits\TraitGeneral;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;

#[Layout('layouts.crm')]
class ListCampana extends EmpresaComponent
{
    use Listing;
    use PermissionsEmpresa;
    use TraitGeneral;
    use WithPagination;

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
        $this->checkPermissions('cupones', ['detail']);
    }

    public function render()
    {
        $query = ConfiguracionCupon::searchAdmin($this->search, [
            'empresa_id' => $this->usuarioEmpresa->empresa_id,
            'status' => $this->status,
            'date_from' => $this->date_from,
            'date_to' => $this->date_to,
        ]);

        $query = $this->applySort($query);

        $cupones = $query->paginate(max(1, min(100, (int) $this->per_page)));

        return view('livewire.empresas.cupones.list-campana', [
            'cupones' => $cupones,
        ]);
    }

    public function updated(string $property): void
    {
        if (in_array($property, ['search', 'status', 'date_from', 'date_to', 'per_page'])) {
            $this->resetPage();
        }
    }

    public function changeStatus(int $id): void
    {
        Access::authorize('cupones', 'edit');

        DB::transaction(function () use ($id) {

            $query = ConfiguracionCupon::searchAdmin('', [
                'empresa_id' => $this->usuarioEmpresa->empresa_id,
            ]);

            $configuracionCupon = $query->whereKey($id)->lockForUpdate()->firstOrFail();

            $inactive = 2;

            $configuracionCupon->estatus = (int) $configuracionCupon->estatus === 1 ? $inactive : 1;

            $configuracionCupon->save();

        });

        $this->dispatch('successEventList', message: 'Estado actualizado.');
    }
}
