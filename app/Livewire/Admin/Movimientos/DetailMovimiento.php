<?php

namespace App\Livewire\Admin\Movimientos;

use App\Models\Movimiento;
use App\Services\Admin\Access;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;

#[Layout('layouts.cms')]
class DetailMovimiento extends Component
{
    #[Locked]
    public ?int $movimiento_id = null;

    public function mount(?int $movimiento_id = null): void
    {
        $this->movimiento_id = $movimiento_id;
        Access::authorize('movimientos', 'detail');
        $movimiento = $this->findMovimiento();
    }

    public function render()
    {
        Access::authorize('movimientos', 'detail');

        return view('livewire.admin.movimientos.detail-movimiento', ['movimiento' => $this->movimiento_id ? $this->findMovimiento() : null, 'capabilities' => Access::capabilities('movimientos')]);
    }

    protected function findMovimiento(): Movimiento
    {
        return Movimiento::searchAdmin()->with([0 => 'empresa', 1 => 'admin'])->findOrFail($this->movimiento_id);
    }
}
