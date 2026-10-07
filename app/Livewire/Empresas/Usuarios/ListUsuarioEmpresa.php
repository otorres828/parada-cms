<?php

namespace App\Livewire\Empresas\Usuarios;

use App\Livewire\Empresas\EmpresaComponent;
use App\Models\UsuarioEmpresa;
use App\Services\Empresa\Access;
use App\Traits\Listing;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;

#[Layout('layouts.crm')]
class ListUsuarioEmpresa extends EmpresaComponent
{
    use Listing;
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
    }

    public function render()
    {
        $query = UsuarioEmpresa::searchAdmin($this->search, [
            'empresa_id' => $this->usuarioEmpresa->empresa_id,
            'status' => $this->status,
            'es_admin' => 1,
        ]);

        return view('livewire.empresas.usuarios.list-usuario-empresa', [
            'usuarios' => $this->applySort($query)->paginate(max(1, min(100, (int) $this->per_page))),
            'usuarioActualId' => $this->usuarioEmpresa->id,
        ]);
    }

    public function boot(): void
    {
        parent::boot();
        Access::authorize('usuarios', 'list');
    }

    public function updated(string $property): void
    {
        if (in_array($property, ['search', 'status', 'per_page'], true)) {
            $this->resetPage();
        }
    }

    public function changeStatus(int $id): void
    {
        Access::authorize('usuarios', 'edit');
        $usuario = UsuarioEmpresa::searchAdmin('', [
            'empresa_id' => $this->usuarioEmpresa->empresa_id,
            'es_admin'   => 1
        ])->findOrFail($id);

        UsuarioEmpresa::exigir($usuario->id !== $this->usuarioEmpresa->id, 'estatus', 'No puedes desactivar tu propia cuenta.');
        $usuario->estatus = $usuario->estatus === UsuarioEmpresa::ESTADO_ACTIVE
            ? UsuarioEmpresa::ESTADO_INACTIVE
            : UsuarioEmpresa::ESTADO_ACTIVE;
        $usuario->save();

        $this->dispatch('empresas_usuario_success', message: 'Estado actualizado.');
    }

    public function deleteUsuario(int $id): void
    {
        Access::authorize('usuarios', 'delete');

        DB::transaction(function () use ($id) {
            $usuario = UsuarioEmpresa::searchAdmin('', [
                'empresa_id' => $this->usuarioEmpresa->empresa_id,
                'es_admin' => 1,
            ])->where('es_admin', 0)->whereKey($id)->lockForUpdate()->firstOrFail();

            $usuario->estatus = UsuarioEmpresa::ESTADO_DELETE;
            $usuario->save();
        });

        $this->resetPage();
        $this->dispatch('empresas_usuario_success', message: 'Usuario eliminado correctamente.');
    }
}
