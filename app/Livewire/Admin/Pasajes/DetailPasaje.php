<?php

namespace App\Livewire\Admin\Pasajes;

use App\Models\Pasaje;
use App\Services\Admin\Access;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;

#[Layout('layouts.cms')]
class DetailPasaje extends Component
{
    public bool $canViewCampaign = false;

    public bool $canViewReservation = false;

    #[Locked]
    public ?int $pasaje_id = null;

    public Pasaje $pasaje;

    public string $qr;

    public function mount(?int $pasaje_id = null): void
    {
        $this->pasaje_id = $pasaje_id;
        $this->canViewCampaign = Access::allows('cupones', 'detail');
        $this->canViewReservation = Access::allows('reservas', 'detail');
        $this->pasaje = $this->findPasaje();
        $this->qr = $this->pasaje->getQr() ?? '';
    }

    public function render()
    {

        return view('livewire.admin.pasajes.detail-pasaje');
    }

    protected function findPasaje(): Pasaje
    {
        return Pasaje::searchAdmin()->with([0 => 'reserva.cupon.configuracionCupon', 1 => 'viajero'])->findOrFail($this->pasaje_id);
    }
}
