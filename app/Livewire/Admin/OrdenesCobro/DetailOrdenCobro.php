<?php

namespace App\Livewire\Admin\OrdenesCobro;

use App\Models\OrdenCobro;
use App\Services\Admin\Access;
use App\Services\Admin\Audit;
use App\Services\OrdenCobroService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;

#[Layout('layouts.cms')]
class DetailOrdenCobro extends Component
{
    #[Locked]
    public int $orden_cobro_id;

    public string $motivo = '';

    public bool $canReview = false;

    public function mount(int $orden_cobro_id): void
    {
        $this->orden_cobro_id = $orden_cobro_id;
        $this->canReview = Access::allows('ordenes-cobro', 'review') ?? false;
    }

    public function render()
    {
        return view('livewire.admin.ordenes-cobro.detail-orden-cobro', [
            'orden' => OrdenCobro::findAdminDetail($this->orden_cobro_id),
        ]);
    }

    public function aprobar(OrdenCobroService $service): void
    {
        Access::authorize('ordenes-cobro', 'review');

        $orden = $service->aprobar($this->orden_cobro_id, (int) Auth::guard('admin')->id());
        Audit::record('orden-cobro.aprobada', $orden);
        $this->dispatch('successEventList', message: 'Orden de cobro aprobada correctamente.');
    }

    public function rechazar(OrdenCobroService $service): void
    {
        Access::authorize('ordenes-cobro', 'review');

        $data = $this->validate([
            'motivo' => ['required', 'string', 'max:2000'],
        ], [], [
            'motivo' => 'Motivo del rechazo',
        ]);

        $orden = $service->rechazar(
            $this->orden_cobro_id,
            (int) Auth::guard('admin')->id(),
            $data['motivo'],
        );

        Audit::record('orden-cobro.rechazada', $orden, ['motivo' => $data['motivo']]);
        $this->motivo = '';
        $this->dispatch('successEventList', message: 'Orden de cobro rechazada.');
    }
}
