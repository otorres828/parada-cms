<?php

namespace App\Livewire\Admin\Reservas;

use App\Models\Reserva;
use App\Services\Admin\Access;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;

#[Layout('layouts.cms')]
class DetailReserva extends Component
{
    #[Locked]
    public ?int $reserva_id = null;

    public function mount(?int $reserva_id = null): void
    {
        $this->reserva_id = $reserva_id;
        Access::authorize('reservas', 'detail');
        $reserva = $this->findReserva();
    }

    public function render()
    {
        Access::authorize('reservas', 'detail');

        return view('livewire.admin.reservas.detail-reserva', ['reserva' => $this->reserva_id ? $this->findReserva() : null, 'capabilities' => Access::capabilities('reservas')]);
    }

    protected function findReserva(): Reserva
    {
        return Reserva::searchAdmin()->with([0 => 'usuario', 1 => 'programacion.viaje.empresa', 2 => 'pasajes.viajero'])->findOrFail($this->reserva_id);
    }
}
