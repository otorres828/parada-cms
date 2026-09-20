<?php

namespace App\Livewire\Admin\Programaciones;

use App\Models\Pasaje;
use App\Models\Programacion;
use App\Services\Admin\Access;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;

#[Layout('layouts.cms')]
class PassengerProgramacion extends Component
{
    #[Locked]
    public ?int $programacion_id = null;

    public Programacion $programacion;

    public Collection $tickets;

    public bool $canReservasDetail = false;

    public bool $canViajesDetail = false;

    public int $capacidad = 0, $ocupados = 0;
    
    public float $ocupacion = 0;

    public function mount(?int $programacion_id = null): void
    {
        $this->programacion_id = $programacion_id;
        Access::authorize('programaciones', 'passengers');
        $programacion = $this->findProgramacion();

        if (!$programacion) {
            abort(404);
        }
        
        $this->programacion = $programacion;

        $this->tickets = Pasaje::getTickets($programacion_id);
        $this->canReservasDetail = Access::allows('reservas', 'detail');
        $this->canViajesDetail = Access::allows('viajes', 'detail');

        $this->capacidad = max(0, (int) $programacion->asientos_totales);
        $this->ocupados = min($this->capacidad, max(0, $this->capacidad - (int) $programacion->asientos_disponibles));
        $this->ocupacion = $this->capacidad > 0 ? round(($this->ocupados * 100) / $this->capacidad, 2) : 0;
    }

    public function render()
    {
        return view('livewire.admin.programaciones.passenger-programacion');
    }

    protected function findProgramacion(): Programacion
    {
        return Programacion::searchAdmin()
            ->with([
                'viaje.empresa',
                'viaje.origenTerminal',
                'viaje.destinoTerminal',
                'viaje.tramos.origenTerminal',
                'viaje.tramos.destinoTerminal',
                'tramoPrecios.origenTerminal',
                'tramoPrecios.destinoTerminal',
            ])
            ->findOrFail($this->programacion_id);
    }
}
