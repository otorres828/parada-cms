<?php

namespace App\Livewire\Admin\Retiros;

use App\Models\Retiro;
use App\Services\Admin\Access;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;

#[Layout('layouts.cms')]
class DetailRetiro extends Component
{
    #[Locked]
    public ?int $retiro_id = null;

    public function mount(?int $retiro_id = null): void
    {
        $this->retiro_id = $retiro_id;
        Access::authorize('retiros', 'detail');
        $retiro = $this->findRetiro();
    }

    public function render()
    {
        Access::authorize('retiros', 'detail');

        return view('livewire.admin.retiros.detail-retiro', ['retiro' => $this->retiro_id ? $this->findRetiro() : null, 'capabilities' => Access::capabilities('retiros')]);
    }

    public function downloadProof()
    {
        Access::authorize('retiros', 'detail');
        $path = $this->findRetiro()->comprobante;
        abort_unless($path && str_starts_with($path, 'comprobantes/') && Storage::disk('local')->exists($path), 404);

        return Storage::disk('local')->download($path);
    }

    protected function findRetiro(): Retiro
    {
        return Retiro::searchAdmin()->with([0 => 'empresa', 1 => 'admin', 2 => 'revisor'])->findOrFail($this->retiro_id);
    }
}
