<?php

namespace App\Livewire\Admin\Reservas;

use App\Models\Reserva;
use App\Services\Admin\Access;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;

#[Layout('layouts.crm')]
class DetailReserva extends Component
{
    public bool $canViewPassengers = false;

    public bool $canViewCampaign = false;

    public bool $canViewTicket = false;

    public bool $canViewReservation = false;

    #[Locked]
    public ?int $reserva_id = null;

    public Reserva $reserva;

    public function mount(?int $reserva_id = null): void
    {
        $this->reserva_id = $reserva_id;
        $this->canViewPassengers = Access::allows('programaciones', 'detail');
        $this->canViewCampaign = Access::allows('cupones', 'detail');
        $this->canViewTicket = Access::allows('pasajes', 'detail');
        $this->canViewReservation = Access::allows('reservas', 'detail');
        $this->reserva = $this->findReserva();
    }

    public function render()
    {
        return view('livewire.admin.reservas.detail-reserva');
    }

    public function reenviarCorreo(): void
    {
        Access::authorize('reservas', 'detail');
        $reserva = $this->findReserva();

        if ($reserva->estado_pago !== Reserva::ESTADO_PAGO_PAGADO) {
            $this->dispatch('admin_reserva_error', message: 'Solo se pueden enviar los pasajes de una reserva pagada.');
            return;
        }

        if (! $reserva->sendMailReserva()) {
            $this->dispatch('admin_reserva_error', message: 'El comprador no tiene un correo válido registrado.');
            return;
        }

        $this->dispatch('admin_reserva_success', message: 'Correo encolado para reenviar el comprobante y los pasajes.');
    }

    protected function findReserva(): Reserva
    {
        return Reserva::findAdminDetail($this->reserva_id);
    }
}
