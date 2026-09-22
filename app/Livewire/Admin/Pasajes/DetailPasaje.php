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
    #[Locked]
    public ?int $pasaje_id = null;

    public Pasaje $pasaje;

    public function mount(?int $pasaje_id = null): void
    {
        $this->pasaje_id = $pasaje_id;
        Access::authorize('pasajes', 'detail');
        $this->pasaje = $this->findPasaje();
    }

    public function render()
    {
        $this->pasaje = $this->findPasaje();

        return view('livewire.admin.pasajes.detail-pasaje', ['qr' => $this->pasaje->getQr()]);
    }

    protected function findPasaje(): Pasaje
    {
        return Pasaje::searchAdmin()->with([0 => 'reserva', 1 => 'viajero'])->findOrFail($this->pasaje_id);
    }
}
