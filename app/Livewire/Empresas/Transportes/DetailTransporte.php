<?php

namespace App\Livewire\Empresas\Transportes;

use App\Models\Transporte;
use App\Models\Programacion;
use App\Models\TipoCambio;
use App\Services\Empresa\Access;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use App\Livewire\Empresas\EmpresaComponent;
use Livewire\WithPagination;

#[Layout('layouts.crm')]
class DetailTransporte extends EmpresaComponent
{
    use WithPagination;

    public int $per_page = 10;

    protected string $paginationTheme = 'bootstrap';

    #[Locked]
    public ?int $transporte_id = null;

    public Transporte $transporte;

    public bool $canViewPassengers = false;

    public bool $canList = false;

    public function mount(?int $transporte_id = null): void
    {
        $this->transporte_id = $transporte_id;
        $this->transporte = $this->findTransporte();
        $this->canList = Access::allows('transportes', 'list');
        $this->canViewPassengers = Access::allows('programaciones', 'detail');
    }

    public function render()
    {

        $programaciones = Programacion::searchAdmin('', [
            'empresa_id' => $this->usuarioEmpresa->empresa_id,
            'transporte_id' => $this->transporte_id,
            'historial_ventas' => true,
        ])
            ->orderByDesc('salida_fecha')
            ->orderByDesc('salida_hora')
            ->orderByDesc('id')
            ->paginate(max(1, min(100, $this->per_page)));

        return view('livewire.empresas.transportes.detail-transporte', [
            'programaciones' => $programaciones,
            'tipoCambioVigente' => TipoCambio::vigente(),
        ]);
    }

    public function updatedPerPage(): void
    {
        $this->resetPage();
    }

    public function findTransporte(): Transporte
    {
        return Transporte::searchAdmin('', [
            'empresa_id' => $this->usuarioEmpresa->empresa_id
        ])->with(['empresa', 'amenidades'])->findOrFail($this->transporte_id);
    }
}
