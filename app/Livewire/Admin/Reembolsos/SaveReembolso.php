<?php

namespace App\Livewire\Admin\Reembolsos;

use App\Models\Pago;
use App\Models\Reembolso;
use App\Services\Admin\Access;
use App\Services\Admin\Finance;
use Illuminate\Support\Facades\Route;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;

#[Layout('layouts.cms')]
class SaveReembolso extends Component
{
    public bool $canList = false;

    #[Locked]
    public ?int $reembolso_id = null;

    public $pago_id = '';

    public string $search_pago_id = '';

    public $motivo = '';

    public function mount(): void
    {
        Access::authorize('reembolsos', $this->reembolso_id ? 'edit' : 'add');
        $this->canList = Access::allows('reembolsos', 'list');
        if ($this->reembolso_id) {
            $this->editar($this->findReembolso());
        }
    }

    public function render()
    {
        Access::authorize('reembolsos', $this->reembolso_id ? 'edit' : 'add');
        $query = Pago::searchAdmin($this->search_pago_id);
        $options_pago_id = (clone $query)->orderBy('referencia')->limit(100)->pluck('referencia', 'id')->all();
        if ($this->pago_id && ! isset($options_pago_id[$this->pago_id])) {
            $selected = Pago::searchAdmin()->find($this->pago_id);
            if ($selected) {
                $options_pago_id[$selected->id] = $selected->referencia;
            }
        }

        return view('livewire.admin.reembolsos.save-reembolso', ['reembolso' => $this->reembolso_id ? $this->findReembolso() : null, 'options_pago_id' => $options_pago_id]);
    }

    public function save()
    {
        Access::authorize('reembolsos', $this->reembolso_id ? 'edit' : 'add');
        $data = $this->validateForm();
        $reembolso = Finance::refund($data);
        session()->flash('admin_success', 'Registro guardado correctamente.');
        $target = Route::has('admin.reembolsos.detail') && Access::allows('reembolsos', 'detail') ? 'detail' : 'list';
        $url = Access::allows('reembolsos', $target) ? route('admin.reembolsos.'.$target, in_array($target, ['list', 'add']) ? [] : ['reembolso_id' => $reembolso->id]) : route('admin.account.profile');

        return $this->redirect($url, navigate: true);
    }

    protected function editar(Reembolso $reembolso): void
    {
        $this->pago_id = $reembolso->pago_id ?? '';
        $this->motivo = $reembolso->motivo ?? '';
    }

    protected function validateForm(): array
    {
        $validated = $this->validate(['pago_id' => ['required', 'integer', 'exists:pagos,id'], 'motivo' => ['required', 'string', 'min:10', 'max:2000']], [], ['pago_id' => 'Pago recibido', 'motivo' => 'Motivo del reembolso total']);
        foreach ($validated as $key => &$value) {
            if ($value === '') {
                $value = null;
            }
        }
        unset($value);

        return $validated;
    }

    protected function findReembolso(): Reembolso
    {
        return Reembolso::searchAdmin()->with([0 => 'empresa', 1 => 'pago.reserva', 2 => 'admin', 3 => 'revisor'])->findOrFail($this->reembolso_id);
    }
}
