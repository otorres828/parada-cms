<?php

namespace App\Livewire\Admin\TasasServicio;

use App\Models\TasaServicio;
use App\Services\Admin\Access;
use App\Services\Admin\Audit;
use App\Traits\Listing;
use App\Traits\Permissions;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.cms')]
class ListTasaServicio extends Component
{
    use Listing, Permissions, WithPagination;

    public string $status = '';

    protected array $queryString = [
        'search' => ['except' => ''],
        'status' => ['except' => ''],
        'per_page' => ['except' => 10],
    ];

    public function mount(): void
    {
        $this->sortColumn = 'monto_minimo';
        $this->sortDirection = 'asc';
        $this->checkPermissions('tasas-servicio');
    }

    public function render()
    {
        $query = TasaServicio::searchAdmin($this->search, [
            'status' => $this->status,
        ]);

        $query = $this->applySort($query);

        $tasas = $query->paginate($this->per_page);

        return view('livewire.admin.tasas-servicio.list-tasa-servicio', [
            'tasas' => $tasas,
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
        Access::authorize('tasas-servicio', 'edit');

        try {

            DB::transaction(function () use ($id) {

                Access::authorize('tasas-servicio', 'edit');

                $tasa = TasaServicio::findOrFail($id);

                $inactive = 2;

                $tasa->estatus = (int) $tasa->estatus === 1 ? $inactive : 1;

                $tasa->save();

                Audit::record('tasa_servicio.estado', $tasa, ['estatus' => $tasa->estatus]);

            });

        } catch (ValidationException $e) {

            $this->dispatch('errorEventList', message: collect($e->errors())->flatten()->first());

            return;
        }

        $this->dispatch('successEventList', message: 'Estado actualizado.');
    }
}
