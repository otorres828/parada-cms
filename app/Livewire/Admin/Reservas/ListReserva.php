<?php

namespace App\Livewire\Admin\Reservas;

use App\Exports\ReservasExport;
use App\Models\Empresa;
use App\Models\Reserva;
use App\Services\Admin\Access;
use App\Traits\Listing;
use App\Traits\Permissions;
use App\Traits\TraitGeneral;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;

#[Layout('layouts.cms')]
class ListReserva extends Component
{
    use Listing;
    use Permissions;
    use TraitGeneral;
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
        'date_to' => ['except' => ''],
    ];

    public function mount(): void
    {
        $this->date_from = $this->date_from ?: self::getDefaultDesde();
        $this->date_to = $this->date_to ?: self::getDefaultHasta();
        $this->sortColumn = 'id';
        $this->sortDirection = 'desc';
        $this->checkPermissions('reservas', ['detail']);
        $this->empresas = Empresa::searchAdmin()->orderBy('nombre')->get();
    }

    public function render()
    {
        $query = Reserva::searchAdmin($this->search, [
            'empresa_id' => $this->empresa_id,
            'status' => $this->status,
            'date_from' => $this->date_from,
            'date_to' => $this->date_to,
        ]);

        $query = $this->applySort($query);

        $reservas = $query->paginate($this->per_page);

        return view('livewire.admin.reservas.list-reserva', [
            'reservas' => $reservas,
        ]);
    }

    public function exportExcel()
    {
        Access::authorize('reservas', 'download');

        $query = Reserva::searchAdmin($this->search, [
            'empresa_id' => $this->empresa_id,
            'status' => $this->status,
            'date_from' => $this->date_from,
            'date_to' => $this->date_to,
        ])->with('cupon')->withCount('pasajes');

        return Excel::download(
            new ReservasExport($this->applySort($query)),
            'reservas-' . now()->format('Y-m-d-His') . '.xlsx',
        );
    }

    public function updated($property): void
    {
        if (in_array($property, ['empresa_id', 'search', 'status', 'date_from', 'date_to', 'per_page'])) {
            $this->resetPage();
        }
    }
}
