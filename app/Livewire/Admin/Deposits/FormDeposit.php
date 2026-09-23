<?php

namespace App\Livewire\Admin\Deposits;

use App\Models\Pago;
use App\Models\Reserva;
use App\Services\Admin\Access;
use App\Services\Admin\Finance;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.cms')]
class FormDeposit extends Component
{
    #[Locked]
    public ?int $pago_id = null;

    public $reserva_id = '';

    public string $search_reserva_id = '';

    public $referencia = '';

    public $metodo = 'transferencia';

    public $fecha_pago = '';

    public $comentario = '';

    use WithFileUploads;

    public $comprobante;

    public function mount(): void
    {
        Access::authorize('pagos', $this->pago_id ? 'edit' : 'add');
        if ($this->pago_id) {
            $this->editar($this->findPago());
        }
        $this->fecha_pago = now()->format('Y-m-d\TH:i');
    }

    public function render()
    {
        Access::authorize('pagos', $this->pago_id ? 'edit' : 'add');
        $query = Reserva::searchAdmin($this->search_reserva_id);
        $query->where('estado_pago', Reserva::ESTADO_PAGO_PAGADO)->whereNotIn('id', Pago::select('reserva_id'));
        $options_reserva_id = (clone $query)->orderBy('codigo_referencia')->limit(100)->pluck('codigo_referencia', 'id')->all();
        if ($this->reserva_id && ! isset($options_reserva_id[$this->reserva_id])) {
            $selected = Reserva::searchAdmin()->find($this->reserva_id);
            if ($selected) {
                $options_reserva_id[$selected->id] = $selected->codigo_referencia;
            }
        }

        return view('livewire.admin.deposits.form-deposit', ['pago' => $this->pago_id ? $this->findPago() : null, 'options_reserva_id' => $options_reserva_id]);
    }

    public function save()
    {
        Access::authorize('pagos', $this->pago_id ? 'edit' : 'add');
        $data = $this->validateForm();
        $this->validate(['comprobante' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120']);
        $path = $this->storeProof();
        try {
            $pago = Finance::payment($data, $path);
        } catch (\Throwable $e) {
            if ($path) {
                Storage::disk('local')->delete($path);
            }
            throw $e;
        }
        session()->flash('admin_success', 'Registro guardado correctamente.');
        $target = Route::has('admin.pagos.detail') && Access::allows('pagos', 'detail') ? 'detail' : 'list';
        $url = Route::has('admin.pagos.'.$target) && Access::allows('pagos', $target) ? route('admin.pagos.'.$target, in_array($target, ['list', 'add']) ? [] : ['pago_id' => $pago->id]) : route('admin.account.profile');

        return $this->redirect($url, navigate: true);
    }

    protected function editar(Pago $pago): void
    {
        $this->reserva_id = $pago->reserva_id ?? '';
        $this->referencia = $pago->referencia ?? '';
        $this->metodo = (string) (is_bool($pago->metodo) ? (int) $pago->metodo : $pago->metodo);
        $this->fecha_pago = $pago->fecha_pago?->format('Y-m-d\TH:i') ?? '';
        $this->comentario = $pago->comentario ?? '';
    }

    protected function validateForm(): array
    {
        $validated = $this->validate(['reserva_id' => ['required', 'integer', 'exists:reservas,id'], 'referencia' => ['required', 'string', 'max:255', Rule::unique('pagos', 'referencia')->ignore($this->pago_id)], 'metodo' => ['required', 'in:transferencia,tarjeta,pasarela,efectivo'], 'fecha_pago' => ['required', 'date', 'before_or_equal:now'], 'comentario' => ['nullable', 'string', 'max:2000']], [], ['reserva_id' => 'Reserva pagada', 'referencia' => 'Referencia bancaria / pasarela', 'metodo' => 'Método', 'fecha_pago' => 'Fecha de recepción', 'comentario' => 'Observaciones']);
        foreach ($validated as $key => &$value) {
            if ($value === '') {
                $value = null;
            }
        }
        unset($value);

        return $validated;
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

    protected function findPago(): Pago
    {
        return Pago::searchAdmin()->with([0 => 'empresa', 1 => 'reserva', 2 => 'admin'])->findOrFail($this->pago_id);
    }
}
