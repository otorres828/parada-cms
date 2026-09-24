<?php

namespace App\Livewire\Admin\Solicitudes;

use App\Models\SolicitudEmpresa;
use App\Services\Admin\Access;
use App\Services\Admin\Audit;
use App\Traits\Listing;
use App\Traits\Permissions;
use Illuminate\Support\Facades\DB;
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

        $solicitudes = $query->paginate($this->per_page);
        $this->recordIdsOnPage = $solicitudes->pluck('id')->all();

        return view('livewire.admin.solicitudes.list-solicitud', [
            'solicitudes' => $solicitudes,
        ]);
    }

    public function updated(string $property): void
    {
        if (in_array($property, ['search', 'estatus', 'per_page'], true)) {
            $this->resetPage();
        }
    }

    public function deleteSolicitud(int $id): void
    {
        Access::authorize('solicitudes', 'delete');

        DB::transaction(function () use ($id) {
            $solicitud = SolicitudEmpresa::query()->lockForUpdate()->findOrFail($id);

            Audit::record('solicitud.eliminada', $solicitud);
            $solicitud->delete();
        });

        $this->selectedRecordIds = array_values(array_diff($this->selectedRecordIds, [$id]));
        $this->dispatch('successEventList', message: 'Solicitud eliminada.');
    }

    public function deleteSolicitudes(): void
    {
        Access::authorize('solicitudes', 'delete');

        $ids = array_values(array_unique(array_map('intval', $this->selectedRecordIds)));

        if ($ids === []) {
            $this->dispatch('errorEventList', message: 'Selecciona al menos una solicitud.');

            return;
        }

        DB::transaction(function () use ($ids) {
            $solicitudes = SolicitudEmpresa::query()
                ->whereKey($ids)
                ->lockForUpdate()
                ->get();

            foreach ($solicitudes as $solicitud) {
                Audit::record('solicitud.eliminada', $solicitud);
                $solicitud->delete();
            }
        });

        $this->selectedRecordIds = [];
        $this->dispatch('successEventList', message: 'Solicitudes eliminadas.');
    }
}
