<?php

namespace App\Livewire\Admin\Amenidades;

use App\Models\Amenidad;
use App\Services\Admin\Access;
use App\Services\Admin\Audit;
use App\Traits\Listing;
use App\Traits\Permissions;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.cms')]
class ListAmenidad extends Component
{
    use Listing;
    use Permissions;
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
        $this->checkPermissions('amenidades');
    }

    public function render()
    {
        $query = Amenidad::searchAdmin($this->search, [
            'status' => $this->status,
        ]);

        $query = $this->applySort($query);

        $amenidades = $query->paginate($this->per_page);

        return view('livewire.admin.amenidades.list-amenidad', [
            'amenidades' => $amenidades,
        ]);
    }

    public function updated($property): void
    {
        if (in_array($property, ['search', 'status', 'per_page'])) {
            $this->resetPage();
        }
    }

    public function changeStatus(int $id): void
    {
        Access::authorize('amenidades', 'edit');

        DB::transaction(function () use ($id) {

            $query = Amenidad::searchAdmin();

            $amenidad = $query->whereKey($id)->lockForUpdate()->firstOrFail();

            $inactive = Amenidad::ESTADO_DELETE;

            $amenidad->estatus = (int) $amenidad->estatus === Amenidad::ESTADO_ACTIVE ? $inactive : Amenidad::ESTADO_ACTIVE;

            $amenidad->save();

            Audit::record('registro.estado', $amenidad, ['estatus' => $amenidad->estatus]);

        });

        $this->dispatch('successEventList', message: 'Estado actualizado.');
    }
}
