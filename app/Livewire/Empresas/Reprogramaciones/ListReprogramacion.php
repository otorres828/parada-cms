<?php

namespace App\Livewire\Empresas\Reprogramaciones;

use App\Livewire\Empresas\EmpresaComponent;
use App\Models\Reserva;
use App\Services\Empresa\Access;
use App\Traits\Listing;
use App\Traits\PermissionsEmpresa;
use App\Traits\TraitGeneral;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;

#[Layout('layouts.crm')]
class ListReprogramacion extends EmpresaComponent
{
    use Listing;
    use PermissionsEmpresa;
    use TraitGeneral;
    use WithPagination;

    public string $date_from = '';

    public string $date_to = '';

    protected array $queryString = [
        'search' => ['except' => ''],
        'per_page' => ['except' => 10],
        'date_from' => ['except' => ''],
        'date_to' => ['except' => ''],
    ];

    public function mount(): void
    {
        $this->date_from = $this->date_from ?: self::getDefaultDesde();
        $this->date_to = $this->date_to ?: self::getDefaultHasta();
        $this->sortColumn = 'id';
        $this->sortDirection = 'desc';
        $this->checkPermissions('reprogramaciones');
        $this->canDetail = Access::allows('reservas', 'detail');
    }

    public function render()
    {
        $query = Reserva::searchAdmin($this->search, [
            'empresa_id' => $this->usuarioEmpresa->empresa_id,
            'date_from' => $this->date_from,
            'date_to' => $this->date_to,
        ]);

        $query->whereNotNull('reprogramacion_id')->with('reservaOriginal');
        $query = $this->applySort($query);

        $reservas = $query->paginate(max(1, min(100, (int) $this->per_page)));

        return view('livewire.empresas.reprogramaciones.list-reprogramacion', [
            'reservas' => $reservas
        ]);
    }

    public function updated(string $property): void
    {
        if (in_array($property, ['search', 'date_from', 'date_to', 'per_page'])) {
            $this->resetPage();
        }
    }
}
