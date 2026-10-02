<?php

namespace App\Livewire\Empresas\DatosBancarios;

use App\Livewire\Empresas\EmpresaComponent;
use App\Models\DatoBancario;
use App\Traits\Listing;
use App\Traits\PermissionsEmpresa;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;

#[Layout('layouts.crm')]
class ListDatoBancario extends EmpresaComponent
{
    use Listing;
    use PermissionsEmpresa;
    use WithPagination;

    public string $status = '';

    public function mount(): void
    {
        $this->sortColumn = 'id';
        $this->sortDirection = 'desc';
        $this->checkPermissions('datos-bancarios');
    }

    public function render()
    {
        $query = DatoBancario::searchAdmin($this->search, [
            'empresa_id' => $this->usuarioEmpresa->empresa_id,
            'status' => $this->status,
        ]);

        return view('livewire.empresas.datos-bancarios.list-dato-bancario', [
            'cuentas' => $this->applySort($query)->paginate(max(1, min(100, (int) $this->per_page))),
        ]);
    }

    public function updated(string $property): void
    {
        if (in_array($property, ['search', 'status', 'per_page'], true)) {
            $this->resetPage();
        }
    }
}
