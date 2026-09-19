<?php

namespace App\Livewire\Admin\Retiros;

use App\Models\Retiro;
use App\Services\Admin\Access;
use App\Services\Admin\Finance;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.cms')]
class ReviewRetiro extends Component
{
    #[Locked]
    public ?int $retiro_id = null;

    use WithFileUploads;

    public string $decision = 'aprobado';

    public string $comentario = '';

    public string $referencia = '';

    public $comprobante;

    public function mount(?int $retiro_id = null): void
    {
        $this->retiro_id = $retiro_id;
        Access::authorize('retiros', 'review');
        $retiro = $this->findRetiro();
        if ($retiro->estatus === 'aprobado') {
            $this->decision = 'pagado';
        }
    }

    public function render()
    {
        Access::authorize('retiros', 'review');

        return view('livewire.admin.retiros.review-retiro', ['retiro' => $this->retiro_id ? $this->findRetiro() : null, 'capabilities' => Access::capabilities('retiros')]);
    }

    public function resolve()
    {
        Access::authorize('retiros', 'review');
        $this->validate(['decision' => 'required|in:aprobado,rechazado,pagado', 'comentario' => 'required|string|min:5|max:2000', 'referencia' => 'required_if:decision,pagado|nullable|string|max:255', 'comprobante' => 'required_if:decision,pagado|nullable|file|mimes:jpg,jpeg,png,pdf|max:5120']);
        $path = $this->storeProof();
        try {
            Finance::review('retiros', $this->retiro_id, $this->decision, $this->comentario, $this->referencia ?: null, $path);
        } catch (\Throwable $e) {
            if ($path) {
                Storage::disk('local')->delete($path);
            }
            throw $e;
        }
        session()->flash('admin_success', 'Resolución registrada.');

        return $this->redirect(route('admin.retiros.'.'detail', in_array('detail', ['list', 'add']) ? [] : ['retiro_id' => $this->retiro_id]), navigate: true);
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

    protected function findRetiro(): Retiro
    {
        return Retiro::searchAdmin()->with([0 => 'empresa', 1 => 'admin', 2 => 'revisor'])->findOrFail($this->retiro_id);
    }
}
