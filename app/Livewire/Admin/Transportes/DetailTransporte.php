<?php

namespace App\Livewire\Admin\Transportes;

use App\Models\Transporte;
use App\Models\Programacion;
use App\Models\TipoCambio;
use App\Services\Admin\Access;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.crm')]
class DetailTransporte extends Component
{
    use WithPagination;

    public int $per_page = 10;

    protected string $paginationTheme = 'bootstrap';

    #[Locked]
    public ?int $transporte_id = null;

    public Transporte $transporte;

    public bool $canViewPassengers = false;

    public function mount(?int $transporte_id = null): void
    {
        $this->transporte_id = $transporte_id;
        $this->transporte = $this->findTransporte();
        $this->canViewPassengers = Access::allows('programaciones', 'passengers');
    }

    public function render()
    {

        $programaciones = Programacion::searchAdmin('', [
            'transporte_id' => $this->transporte_id,
            'historial_ventas' => true,
        ])
            ->orderByDesc('salida_fecha')
            ->orderByDesc('salida_hora')
            ->orderByDesc('id')
            ->paginate(max(1, min(100, $this->per_page)));

        return view('livewire.admin.transportes.detail-transporte', [
            'programaciones' => $programaciones,
            'tipoCambioVigente' => TipoCambio::vigente(),
        ]);
    }

    public function updatedPerPage(): void
    {
        $this->resetPage();
    }

    protected function findTransporte(): Transporte
    {
        return Transporte::searchAdmin()->with([0 => 'empresa', 1 => 'amenidades'])->findOrFail($this->transporte_id);
    }
}
