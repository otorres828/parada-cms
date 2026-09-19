<?php

namespace App\Livewire\Admin\Pagos;

use App\Models\Pago;
use App\Services\Admin\Access;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;

#[Layout('layouts.cms')]
class DetailPago extends Component
{
    #[Locked]
    public ?int $pago_id = null;

    public function mount(?int $pago_id = null): void
    {
        $this->pago_id = $pago_id;
        Access::authorize('pagos', 'detail');
        $pago = $this->findPago();
    }

    public function render()
    {
        Access::authorize('pagos', 'detail');

        return view('livewire.admin.pagos.detail-pago', ['pago' => $this->pago_id ? $this->findPago() : null, 'capabilities' => Access::capabilities('pagos')]);
    }

    public function downloadProof()
    {
        Access::authorize('pagos', 'detail');
        $path = $this->findPago()->comprobante;
        abort_unless($path && str_starts_with($path, 'comprobantes/') && Storage::disk('local')->exists($path), 404);

        return Storage::disk('local')->download($path);
    }

    protected function findPago(): Pago
    {
        return Pago::searchAdmin('', ['con_pasajeros' => true])->findOrFail($this->pago_id);
    }
}
