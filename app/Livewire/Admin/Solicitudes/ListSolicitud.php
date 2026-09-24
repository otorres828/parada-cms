<?php

namespace App\Livewire\Admin\Solicitudes;

use App\Models\SolicitudEmpresa;
use App\Traits\Listing;
use App\Traits\Permissions;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.cms')]
class ListSolicitud extends Component
{
    use Listing;
    use Permissions;
    use WithPagination;

    public string $estatus = '';

    protected array $queryString = [
        'search' => ['except' => ''],
        'per_page' => ['except' => 10],
        'estatus' => ['except' => ''],
    ];

    public function mount(): void
    {
        $this->sortColumn = 'id';
        $this->sortDirection = 'desc';
        $this->checkPermissions('solicitudes', ['detail']);
    }

    public function render()
    {
        $query = SolicitudEmpresa::searchAdmin($this->search, [
            'estatus' => $this->estatus,
        ]);

        $query = $this->applySort($query);

        return view('livewire.admin.solicitudes.list-solicitud', [
            'solicitudes' => $query->paginate($this->per_page),
        ]);
    }

    public function updated(string $property): void
    {
        if (in_array($property, ['search', 'estatus', 'per_page'], true)) {
            $this->resetPage();
        }
    }
}
