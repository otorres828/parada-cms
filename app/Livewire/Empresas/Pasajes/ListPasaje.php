<?php

namespace App\Livewire\Empresas\Pasajes;

use App\Exports\Empresas\PasajesExport;
use App\Livewire\Empresas\EmpresaComponent;
use App\Models\Pasaje;
use App\Models\Empresa;
use App\Services\Empresa\Access;
use App\Traits\Listing;
use App\Traits\PermissionsEmpresa;
use App\Traits\TraitGeneral;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;

#[Layout('layouts.crm')]
class ListPasaje extends EmpresaComponent
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
        $this->checkPermissions('pasajes', ['detail']);
    }

    public function render()
    {
        $query = Pasaje::searchAdmin($this->search, [
            'empresa_id' => $this->usuarioEmpresa->empresa_id,
            'estado_pago' => $this->status,
            'date_from' => $this->date_from,
            'date_to' => $this->date_to,
        ]);

        $query = $this->applySort($query);

        $pasajes = $query->paginate(max(1, min(100, (int) $this->per_page)));

        return view('livewire.empresas.pasajes.list-pasaje', [
            'pasajes' => $pasajes
        ]);
    }

    public function exportExcel()
    {
        Access::authorize('pasajes', 'download');

        $query = Pasaje::searchAdmin($this->search, [
            'empresa_id' => $this->usuarioEmpresa->empresa_id,
            'estado_pago' => $this->status,
            'date_from' => $this->date_from,
            'date_to' => $this->date_to,
        ]);

        return Excel::download(
            new PasajesExport(
                $this->applySort($query),
                (int) $this->usuarioEmpresa->empresa->tipo_contrato === Empresa::CONTRATO_ELLOS_RECIBEN,
            ),
            'pasajes-'.now()->format('Y-m-d-His').'.xlsx',
        );
    }

    public function updated(string $property): void
    {
        if (in_array($property, ['search', 'status', 'date_from', 'date_to', 'per_page'])) {
            $this->resetPage();
        }
    }
}
