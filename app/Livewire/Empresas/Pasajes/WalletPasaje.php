<?php

namespace App\Livewire\Empresas\Pasajes;

use App\Livewire\Empresas\EmpresaComponent;
use App\Models\Pasaje;
use App\Services\Empresa\Access;
use App\Services\GoogleWalletService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use RuntimeException;

#[Layout('layouts.crm')]
class WalletPasaje extends EmpresaComponent
{
    #[Locked]
    public bool $canViewCampaign = false;

    #[Locked]
    public bool $canViewReservation = false;

    #[Locked]
    public ?int $pasaje_id = null;

    #[Locked]
    public Pasaje $pasaje;

    #[Locked]
    public ?string $walletUrl = null;

    #[Locked]
    public ?string $walletError = null;

    public string $qr;

    public function mount(?int $pasaje_id = null): void
    {
        $this->pasaje_id = $pasaje_id;
        $this->canViewCampaign = Access::allows('cupones', 'detail');
        $this->canViewReservation = Access::allows('reservas', 'detail');
        $this->pasaje = $this->findPasaje();
        $this->qr = $this->pasaje->getQr() ?? '';

        try {
            $this->walletUrl = GoogleWalletService::generarUrl($this->pasaje);
        } catch (RuntimeException $exception) {
            $this->walletError = $exception->getMessage();
        }
    }

    public function render()
    {
        return view('livewire.empresas.pasajes.wallet-pasaje');
    }

    protected function findPasaje(): Pasaje
    {
        return Pasaje::searchAdmin('', ['empresa_id' => $this->usuarioEmpresa->empresa_id])
            ->with([
                'reserva.cupon.configuracionCupon',
                'reserva.tipoCambio',
                'reserva.origenTerminal',
                'reserva.destinoTerminal',
                'reserva.tramoPrecio',
                'reserva.programacion.transporte',
                'reserva.programacion.viaje.empresa',
            ])
            ->findOrFail($this->pasaje_id);
    }
}