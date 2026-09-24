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
    public bool $canViewPassengers = false;

    public bool $canViewCampaign = false;

    public bool $canViewTicket = false;

    #[Locked]
    public ?int $reserva_id = null;

    public Reserva $reserva;

    public function mount(?int $reserva_id = null): void
    {
        $this->reserva_id = $reserva_id;
        $this->canViewPassengers = Access::allows('programaciones', 'passengers');
        $this->canViewCampaign = Access::allows('cupones', 'detail');
        $this->canViewTicket = Access::allows('pasajes', 'detail');
        $this->reserva = $this->findReserva();
    }

    public function render()
    {
        return view('livewire.admin.reservas.detail-reserva');
    }

    protected function findReserva(): Reserva
    {
        return Reserva::findAdminDetail($this->reserva_id);
    }
}
