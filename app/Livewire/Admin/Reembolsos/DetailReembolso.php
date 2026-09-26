<?php

namespace App\Livewire\Admin\Reembolsos;

use App\Models\Reembolso;
use App\Services\Admin\Access;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;

#[Layout('layouts.cms')]
class DetailReembolso extends Component
{
    #[Locked]
    public ?int $reembolso_id = null;

    public function mount(?int $reembolso_id = null): void
    {
        $this->reembolso_id = $reembolso_id;
        $reembolso = $this->findReembolso();
    }

    public function render()
    {
        return view('livewire.admin.reembolsos.detail-reembolso', ['reembolso' => $this->reembolso_id ? $this->findReembolso() : null]);
    }

    public function downloadProof()
    {
        Access::authorize('reembolsos', 'detail');

        $path = $this->findReembolso()->comprobante;
        abort_unless($path && str_starts_with($path, 'comprobantes/') && Storage::disk('local')->exists($path), 404);

        return Storage::disk('local')->download($path);
    }

    protected function findReembolso(): Reembolso
    {
        return Reembolso::searchAdmin()->with([0 => 'empresa', 1 => 'pagoReserva.reserva.tipoCambio', 2 => 'admin', 3 => 'revisor'])->findOrFail($this->reembolso_id);
    }
}
