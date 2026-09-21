<?php

namespace App\Livewire\Admin\Terminales;

use App\Models\Terminal;
use App\Services\Admin\Access;
use App\Services\Admin\Audit;
use App\Traits\Listing;
use App\Traits\Permissions;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.cms')]
class ListTerminal extends Component
{
    use Listing;
    use Permissions;
    use WithPagination;

    public string $status = '';

    protected array $queryString = [
        'search' => ['except' => ''], 
        'per_page' => ['except' => 10], 
        'status' => ['except' => '']
    ];

    public function mount(): void
    {
        $this->sortColumn = 'id';
        $this->sortDirection = 'desc';
        $this->checkPermissions('terminales');
    }

    public function render()
    {
        $query = Terminal::searchAdmin($this->search, ['status' => $this->status]);
        $query = $this->applySort($query);
        $terminales = $query->paginate($this->per_page);
        return view('livewire.admin.terminales.list-terminal', ['terminales' => $terminales, 'capabilities' => Access::capabilities('terminales')]);
    }

    public function updated($property): void
    {
        if (in_array($property, ['search', 'status', 'per_page'])) {
            $this->resetPage();
        }
    }

    public function changeStatus(int $id): void
    {
        Access::authorize('terminales', 'edit');

        DB::transaction(function () use ($id) {

            $query = Terminal::searchAdmin();

            $terminal = $query->whereKey($id)->lockForUpdate()->firstOrFail();

            $inactive = Terminal::ESTADO_INACTIVE;

            $terminal->estatus = (int) $terminal->estatus === Terminal::ESTADO_ACTIVE ? $inactive : Terminal::ESTADO_INACTIVE;

            $terminal->save();

            Audit::record('registro.estado', $terminal, ['estatus' => $terminal->estatus]);

        });
        $this->dispatch('successEventList', message: 'Estado actualizado.');
    }
}
