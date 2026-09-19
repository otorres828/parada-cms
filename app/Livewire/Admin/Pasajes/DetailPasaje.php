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

    public function mount(?int $pasaje_id = null): void
    {
        $this->pasaje_id = $pasaje_id;
        Access::authorize('pasajes', 'detail');
        $pasaje = $this->findPasaje();
    }

    public function render()
    {
        Access::authorize('pasajes', 'detail');

        return view('livewire.admin.pasajes.detail-pasaje', ['pasaje' => $this->pasaje_id ? $this->findPasaje() : null, 'capabilities' => Access::capabilities('pasajes')]);
    }

    protected function findPasaje(): Pasaje
    {
        return Pasaje::searchAdmin()->with([0 => 'reserva', 1 => 'viajero'])->findOrFail($this->pasaje_id);
    }
}
