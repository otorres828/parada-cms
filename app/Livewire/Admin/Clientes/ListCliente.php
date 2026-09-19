<?php

namespace App\Livewire\Admin\Clientes;

use App\Models\User;
use App\Services\Admin\Access;
use App\Services\Admin\Audit;
use App\Traits\Listing;
use App\Traits\Permissions;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.cms')]
class ListCliente extends Component
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
        $this->checkPermissions('clientes',['detail']);
    }

    public function render()
    {
        $query = User::searchAdmin($this->search, ['status' => $this->status]);

        $query = $this->applySort($query);

        $users = $query->paginate($this->per_page);

        return view('livewire.admin.clientes.list-cliente', [
            'users' => $users, 
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
        Access::authorize('clientes', 'edit');
        DB::transaction(function () use ($id) {
            $user = User::findOrFail($id);
            $inactive = 2;
            $user->status = (int) $user->status === 1 ? $inactive : 1;
            $user->save();
            Audit::record('registro.estado', $user, ['status' => $user->status]);
        });
        $this->dispatch('successEventList', message: 'Estado actualizado.');
    }
}
