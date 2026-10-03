<?php

namespace App\Livewire\Empresas\Reservas;

use App\Exports\ReservasExport;
use App\Livewire\Empresas\EmpresaComponent;
use App\Models\Reserva;
use App\Models\Empresa;
use App\Services\Empresa\Access;
use App\Traits\Listing;
use App\Traits\PermissionsEmpresa;
use App\Traits\TraitGeneral;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;

#[Layout('layouts.crm')]
class ListReserva extends EmpresaComponent
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
        $this->checkPermissions('reservas', ['detail']);
    }

    public function render()
    {
        $query = Reserva::searchAdmin($this->search, [
            'empresa_id' => $this->usuarioEmpresa->empresa_id,
            'status' => $this->status,
            'date_from' => $this->date_from,
            'date_to' => $this->date_to,
        ]);

        $query = $this->applySort($query);

        $reservas = $query->paginate(max(1, min(100, (int) $this->per_page)));

        return view('livewire.empresas.reservas.list-reserva', [
            'reservas' => $reservas
        ]);
    }

    public function exportExcel()
    {
        Access::authorize('reservas', 'download');

        $query = Reserva::searchAdmin($this->search, [
            'empresa_id' => $this->usuarioEmpresa->empresa_id,
            'status' => $this->status,
            'date_from' => $this->date_from,
            'date_to' => $this->date_to,
        ])->with(['cupon', 'reservaOriginal']);

        return Excel::download(
            new ReservasExport($this->applySort($query), ['empresa']),
            'reservas-'.now()->format('Y-m-d-His').'.xlsx',
        );
    }

    public function updated(string $property): void
    {
        if (in_array($property, ['search', 'status', 'date_from', 'date_to', 'per_page'])) {
            $this->resetPage();
        }
    }
}
