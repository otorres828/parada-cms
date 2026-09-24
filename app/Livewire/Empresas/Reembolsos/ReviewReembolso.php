<?php

namespace App\Livewire\Empresas\Reembolsos;

use App\Models\Reembolso;
use App\Services\Admin\Access;
use App\Services\Admin\Finance;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.cms')]
class ReviewReembolso extends Component
{
    #[Locked]
    public ?int $reembolso_id = null;

    use WithFileUploads;

    public string $decision = 'aprobado';

    public string $comentario = '';

    public string $referencia = '';

    public $comprobante;

    public function mount(?int $reembolso_id = null): void
    {
        $this->reembolso_id = $reembolso_id;
        $reembolso = $this->findReembolso();
        if ($reembolso->estatus === 'aprobado') {
            $this->decision = 'pagado';
        }
    }

    public function render()
    {
        return view('livewire.empresas.reembolsos.review-reembolso', ['reembolso' => $this->reembolso_id ? $this->findReembolso() : null]);
    }

    public function resolve()
    {
        Access::authorize('reembolsos', 'review');
        $this->validate([
            'decision' => 'required|in:aprobado,rechazado,pagado',
            'comentario' => 'required|string|min:5|max:2000',
            'referencia' => 'required_if:decision,pagado|nullable|string|max:255',
            'comprobante' => 'required_if:decision,pagado|nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);
        $path = $this->storeProof();
        try {
            Finance::review('reembolsos', $this->reembolso_id, $this->decision, $this->comentario, $this->referencia ?: null, $path);
        } catch (\Throwable $e) {
            if ($path) {
                Storage::disk('local')->delete($path);
            }
            throw $e;
        }
        session()->flash('admin_success', 'Resolución registrada.');

        return $this->redirect(route('empresas.reembolsos.list'), navigate: true);
    }

    protected function storeProof(): ?string
    {
        if (! $this->comprobante) {
            return null;
        }
        $path = $this->comprobante->store('comprobantes', 'local');
        if (! $path) {
            throw ValidationException::withMessages(['comprobante' => 'No se pudo guardar el comprobante.']);
        }

        return $path;
    }

    protected function findReembolso(): Reembolso
    {
        return Reembolso::searchAdmin()->with([0 => 'empresa', 1 => 'pagoReserva.reserva', 2 => 'admin', 3 => 'revisor'])->findOrFail($this->reembolso_id);
    }
}
