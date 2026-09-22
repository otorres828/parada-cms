<?php

namespace App\Livewire\Admin\Autobuses;

use App\Models\Autobus;
use App\Models\Programacion;
use App\Services\Admin\Access;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.cms')]
class DetailAutobus extends Component
{

    use WithPagination;

    public int $per_page = 10;

    protected string $paginationTheme = 'bootstrap';
    
    #[Locked]
    public ?int $autobus_id = null;

    public  Autobus $autobus;

    public bool $canViewPassengers = false;
    

    public function mount(?int $autobus_id = null): void
    {
        $this->autobus_id = $autobus_id;
        Access::authorize('autobuses', 'detail');
        $this->autobus = $this->findAutobus();
        $this->canViewPassengers = Access::allows('programaciones', 'passengers');
    }

    public function render()
    {

        $programaciones = Programacion::searchAdmin('', [
            'autobus_id' => $this->autobus_id,
            'historial_ventas' => true,
        ])
        ->orderByDesc('fecha_salida')
        ->orderByDesc('hora_salida')
        ->orderByDesc('id')
        ->paginate(max(1, min(100, $this->per_page)));

        return view('livewire.admin.autobuses.detail-autobus', [
            'programaciones' => $programaciones
        ]);
    }

    public function updatedPerPage(): void
    {
        $this->resetPage();
    }

    protected function findAutobus(): Autobus
    {
        return Autobus::searchAdmin()->with([0 => 'empresa', 1 => 'amenidades'])->findOrFail($this->autobus_id);
    }
}
