<?php

namespace App\Livewire\Admin\Reembolsos;

use App\Models\PagoReserva;
use App\Models\Reembolso;
use App\Services\Admin\Access;
use App\Services\Admin\Finance;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;

#[Layout('layouts.cms')]
class SaveReembolso extends Component
{
    public Reembolso $reembolso;

    #[Locked]
    public ?int $reembolso_id = null;

    public $pago_reserva_id = '';

    public string $search_pago_reserva_id = '';

    public $motivo = '';

    public function mount(): void
    {
        if ($this->reembolso_id) {
            $this->editar($this->findReembolso());
        }
    }

    public function render()
    {
        $query = PagoReserva::searchAdmin($this->search_pago_reserva_id);
        $options_pago_reserva_id = (clone $query)->orderBy('referencia_pago')->limit(100)->pluck('referencia_pago', 'id')->all();
        if ($this->pago_reserva_id && ! isset($options_pago_reserva_id[$this->pago_reserva_id])) {
            $selected = PagoReserva::searchAdmin()->find($this->pago_reserva_id);
            if ($selected) {
                $options_pago_reserva_id[$selected->id] = $selected->referencia_pago;
            }
        }

        return view('livewire.admin.reembolsos.save-reembolso', ['options_pago_reserva_id' => $options_pago_reserva_id]);
    }

    public function save()
    {
        Access::authorize('reembolsos', $this->reembolso_id ? 'edit' : 'add');
        $data = $this->validateForm();
        $reembolso = Finance::refund($data);
        session()->flash('admin_success', 'Registro guardado correctamente.');

        return $this->redirect(route('admin.reembolsos.list'), navigate: true);
    }

    protected function editar(Reembolso $reembolso): void
    {
        $this->reembolso = $reembolso;
        $this->pago_reserva_id = $reembolso->pago_reserva_id ?? '';
        $this->motivo = $reembolso->motivo ?? '';
    }

    protected function validateForm(): array
    {
        $validated = $this->validate([
            'pago_reserva_id' => ['required', 'integer', 'exists:pagos_reservas,id'],
            'motivo' => ['required', 'string', 'min:10', 'max:2000'],
        ], [], [
            'pago_reserva_id' => 'Pago recibido',
            'motivo' => 'Motivo del reembolso total',
        ]);
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
        return Reembolso::searchAdmin()->with([0 => 'empresa', 1 => 'pagoReserva.reserva', 2 => 'admin', 3 => 'revisor'])->findOrFail($this->reembolso_id);
    }
}
