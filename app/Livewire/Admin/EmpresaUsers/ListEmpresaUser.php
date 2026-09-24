<?php

namespace App\Livewire\Admin\EmpresaUsers;

use App\Models\Empresa;
use App\Models\UsuarioEmpresa;
use App\Services\Admin\Access;
use App\Services\Admin\Audit;
use App\Traits\Listing;
use App\Traits\Permissions;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.cms')]
class ListEmpresaUser extends Component
{
    #[Locked]
    public ?int $empresa_id = null;

    use Listing;
    use Permissions;
    use WithPagination;

    public string $status = '';

    protected array $queryString = [
        'search' => ['except' => ''],
        'per_page' => ['except' => 10],
        'status' => ['except' => ''],
    ];

    public function mount(?int $empresa_id = null): void
    {
        $this->empresa_id = $empresa_id;
        Empresa::findOrFail($empresa_id);
        $this->sortColumn = 'id';
        $this->sortDirection = 'desc';
        $this->checkPermissions('empresas.users', ['detail', 'permissions']);
    }

    public function render()
    {
        $query = UsuarioEmpresa::searchAdmin($this->search, [
            'status' => $this->status,
            'empresa_id' => $this->empresa_id,
        ]);

        $query = $this->applySort($query);

        $usuariosEmpresa = $query->paginate($this->per_page);

        return view('livewire.admin.empresa-users.list-empresa-user', [
            'usuariosEmpresa' => $usuariosEmpresa,
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
        Access::authorize('empresas.users', 'edit');

        DB::transaction(function () use ($id) {

            $query = UsuarioEmpresa::searchAdmin();

            $query->where('empresa_id', $this->empresa_id);

            $usuarioEmpresa = $query->whereKey($id)->lockForUpdate()->firstOrFail();

            $inactive = 2;

            $usuarioEmpresa->estatus = (int) $usuarioEmpresa->estatus === 1 ? $inactive : 1;

            $usuarioEmpresa->save();

            Audit::record('registro.estado', $usuarioEmpresa, ['estatus' => $usuarioEmpresa->estatus]);

        });
        $this->dispatch('successEventList', message: 'Estado actualizado.');
    }
}
