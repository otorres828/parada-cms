<?php

namespace App\Livewire\Admin\OrdenesCobro;

use App\Exports\OrdenesCobroExport;
use App\Models\Empresa;
use App\Models\OrdenCobro;
use App\Services\Admin\Access;
use App\Support\ConversorMoneda;
use App\Traits\Listing;
use App\Traits\Permissions;
use App\Traits\TraitGeneral;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

#[Layout('layouts.cms')]
class ListOrdenCobro extends Component
{
    use Listing;
    use Permissions;
    use TraitGeneral;
    use WithPagination;

    public string $empresa_id = '';

    public string $estatus = '';

    public string $date_from = '';

    public string $date_to = '';

    protected array $queryString = [
        'search' => ['except' => ''],
        'empresa_id' => ['except' => ''],
        'estatus' => ['except' => ''],
        'date_from' => ['except' => ''],
        'date_to' => ['except' => ''],
    ];

    public function mount(): void
    {
        $this->sortColumn = 'id';
        $this->sortDirection = 'desc';
        $this->date_from = $this->date_from ?: self::getDefaultDesde();
        $this->date_to = $this->date_to ?: self::getDefaultHasta();
        $this->checkPermissions('ordenes-cobro', ['detail', 'review']);
    }

    public function render()
    {
        $query = OrdenCobro::searchAdmin($this->search, [
            'empresa_id' => $this->empresa_id,
            'estatus' => $this->estatus,
            'date_from' => $this->date_from,
            'date_to' => $this->date_to,
        ]);

        $ordenes = $this->applySort($query)->paginate($this->per_page);

        return view('livewire.admin.ordenes-cobro.list-orden-cobro', [
            'ordenes' => $ordenes,
            'conversionesBs' => ConversorMoneda::ordenes($ordenes->getCollection()),
            'empresas' => Empresa::searchAdmin()->orderBy('nombre')->get(['id', 'nombre']),
        ]);
    }

    public function updated(string $property): void
    {
        if (in_array($property, ['search', 'empresa_id', 'estatus', 'date_from', 'date_to', 'per_page'], true)) {
            $this->resetPage();
        }
    }

    public function exportExcel(): BinaryFileResponse
    {
        Access::authorize('ordenes-cobro', 'download');

        $query = OrdenCobro::searchAdmin($this->search, [
            'empresa_id' => $this->empresa_id,
            'estatus' => $this->estatus,
            'date_from' => $this->date_from,
            'date_to' => $this->date_to,
        ]);

        return Excel::download(
            new OrdenesCobroExport($this->applySort($query)),
            'ordenes-cobro-'.now()->format('Y-m-d-His').'.xlsx',
        );
    }
}
