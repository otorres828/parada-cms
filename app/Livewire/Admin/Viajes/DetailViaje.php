<?php

namespace App\Livewire\Admin\Viajes;

use App\Models\Programacion;
use App\Models\Viaje;
use App\Services\Admin\Access;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;

#[Layout('layouts.cms')]
class DetailViaje extends Component
{
    #[Locked]
    public ?int $viaje_id = null;

    public $canViewPassengers= false;

    public Viaje $viaje;

    public Collection $programaciones;

    public function mount(?int $viaje_id = null): void
    {
        $this->viaje_id = $viaje_id;
        Access::authorize('viajes', 'detail');
        $viaje = Viaje::searchAdmin()->with([
            'empresa',
            'origenTerminal',
            'destinoTerminal',
            'tramos.origenTerminal',
            'tramos.destinoTerminal',
            'programaciones.tramoPrecios.origenTerminal',
            'programaciones.tramoPrecios.destinoTerminal',
        ])->find($viaje_id);
        if (!$viaje) {
            abort(404);
        }
        $this->viaje = $viaje;
        $this->programaciones = Programacion::searchDetailViajes($viaje_id);
        $this->canViewPassengers = Access::allows('programaciones', 'passengers');
    }

    public function render()
    {
        return view('livewire.admin.viajes.detail-viaje');
    }


}
