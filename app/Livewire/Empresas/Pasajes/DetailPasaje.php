<?php

namespace App\Livewire\Empresas\Pasajes;

use App\Livewire\Empresas\EmpresaComponent;
use App\Models\Pasaje;
use App\Services\Admin\Access;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;

#[Layout('layouts.crm')]
class DetailPasaje extends EmpresaComponent
{

    #[Locked]
    public bool $canViewCampaign = false;

    #[Locked]
    public bool $canViewReservation = false;

    #[Locked]
    public ?int $pasaje_id = null;

    #[Locked]
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
        return view('livewire.empresas.pasajes.detail-pasaje');
    }

    protected function findPasaje(): Pasaje
    {
        return Pasaje::searchAdmin('', ['empresa_id' => $this->usuarioEmpresa->empresa_id])
            ->with([0 => 'reserva.cupon.configuracionCupon', 1 => 'reserva.tipoCambio'])
            ->findOrFail($this->pasaje_id);
    }
}
