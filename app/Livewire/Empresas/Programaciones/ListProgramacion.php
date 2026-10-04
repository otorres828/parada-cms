<?php

namespace App\Livewire\Empresas\Programaciones;

use App\Livewire\Empresas\EmpresaComponent;
use App\Models\Programacion;
use App\Services\Empresa\Access;
use App\Traits\Listing;
use App\Traits\PermissionsEmpresa;
use App\Traits\TraitGeneral;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;

#[Layout('layouts.crm')]
class ListProgramacion extends EmpresaComponent
{
    use Listing;
    use PermissionsEmpresa;
    use TraitGeneral;
    use WithPagination;

    public string $status = '';

    public string $date_from = '';

    public string $date_to = '';

    public bool $canViewPassengers = false;

    protected array $queryString = [
        'search' => ['except' => ''],
        'per_page' => ['except' => 10],
        'status' => ['except' => ''],
        'date_from' => ['except' => ''],
        'date_to' => ['except' => ''],
    ];

    public function mount(): void
    {
        $this->date_from = $this->date_from ?: self::getDefaultDesde();
        $this->date_to = $this->date_to ?: self::getDefaultHasta();
        $this->sortColumn = 'id';
        $this->sortDirection = 'desc';
        $this->checkPermissions('programaciones');

        $this->canViewPassengers = Access::allows('programaciones', 'detail');
    }

    public function render()
    {
        $query = Programacion::searchAdmin($this->search, [
            'empresa_id' => $this->usuarioEmpresa->empresa_id,
            'status' => $this->status,
            'date_from' => $this->date_from,
            'date_to' => $this->date_to,
        ]);

        if (in_array($this->sortColumn, ['salida_fecha', 'salida_hora'], true)) {
            $query->orderBy($this->sortColumn, $this->sortDirection === 'asc' ? 'asc' : 'desc');
        } else {
            $query = $this->applySort($query);
        }

        $query->withExists('reservas');

        $programaciones = $query->paginate(max(1, min(100, (int) $this->per_page)));

        return view('livewire.empresas.programaciones.list-programacion', [
            'programaciones' => $programaciones,
        ]);
    }

    public function changeStatus(int $id): void
    {
        Access::authorize('programaciones', 'edit');

        DB::transaction(function () use ($id) {
            $registro = Programacion::searchAdmin('', [
                'empresa_id' => $this->usuarioEmpresa->empresa_id,
            ])->whereKey($id)->lockForUpdate()->firstOrFail();
            Programacion::exigir(in_array($registro->estatus, [Programacion::ESTADO_PROGRAMADO, Programacion::ESTADO_INACTIVO], true), 'estatus', 'Una programación finalizada no puede cambiar de estatus.');
            $registro->estatus = $registro->estatus === Programacion::ESTADO_ACTIVE
                ? Programacion::ESTADO_INACTIVE
                : Programacion::ESTADO_ACTIVE;
            $registro->save();
        });

        $this->dispatch('successEventList', message: 'Estado actualizado.');
    }

    public function updated(string $property): void
    {
        if (in_array($property, ['search', 'status', 'date_from', 'date_to', 'per_page'])) {
            $this->resetPage();
        }
    }
}
