<?php

namespace App\Livewire\Admin\Programaciones;

use App\Models\Pasaje;
use App\Models\Programacion;
use App\Services\Admin\Access;
use App\Services\ReservaService;
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

    }

    public function render()
    {
        $this->programacion = $this->findProgramacion();
        $this->tickets = Pasaje::getTickets($this->programacion_id);
        $disponibilidad = ReservaService::consultarDisponibilidadPorTramos(new Collection([$this->programacion]));

        return view('livewire.admin.programaciones.passenger-programacion', [
            'disponibilidadTramos' => $disponibilidad[$this->programacion_id],
            'capacidad' => max(0, min((int) $this->programacion->asientos_totales, (int) $this->programacion->autobus?->total_asientos)),
        ]);
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
