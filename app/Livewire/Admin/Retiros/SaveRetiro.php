<?php

namespace App\Livewire\Admin\Retiros;

use App\Models\Empresa;
use App\Models\Retiro;
use App\Services\Admin\Access;
use App\Services\Admin\Finance;
use Illuminate\Support\Facades\Route;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;

#[Layout('layouts.cms')]
class SaveRetiro extends Component
{
    #[Locked]
    public ?int $retiro_id = null;

    public $empresa_id = '';

    public string $search_empresa_id = '';

    public $monto = '';

    public $datos_bancarios = '';

    public $comentario = '';

    public function mount(): void
    {
        Access::authorize('retiros', $this->retiro_id ? 'edit' : 'add');
        if ($this->retiro_id) {
            $this->editar($this->findRetiro());
        }
    }

    public function render()
    {
        Access::authorize('retiros', $this->retiro_id ? 'edit' : 'add');
        $query = Empresa::searchAdmin($this->search_empresa_id);
        $options_empresa_id = (clone $query)->orderBy('nombre')->limit(100)->pluck('nombre', 'id')->all();
        if ($this->empresa_id && ! isset($options_empresa_id[$this->empresa_id])) {
            $selected = Empresa::searchAdmin()->find($this->empresa_id);
            if ($selected) {
                $options_empresa_id[$selected->id] = $selected->nombre;
            }
        }

        return view('livewire.admin.retiros.save-retiro', ['retiro' => $this->retiro_id ? $this->findRetiro() : null, 'capabilities' => Access::capabilities('retiros'), 'options_empresa_id' => $options_empresa_id]);
    }

    public function save()
    {
        Access::authorize('retiros', $this->retiro_id ? 'edit' : 'add');
        $data = $this->validateForm();
        $retiro = Finance::withdrawal($data);
        session()->flash('admin_success', 'Registro guardado correctamente.');
        $target = Route::has('admin.retiros.detail') && Access::allows('retiros', 'detail') ? 'detail' : 'list';
        $url = Access::allows('retiros', $target) ? route('admin.retiros.'.$target, in_array($target, ['list', 'add']) ? [] : ['retiro_id' => $retiro->id]) : route('admin.account.profile');

        return $this->redirect($url, navigate: true);
    }

    protected function editar(Retiro $retiro): void
    {
        $this->empresa_id = $retiro->empresa_id ?? '';
        $this->monto = $retiro->monto ?? '';
        $this->datos_bancarios = $retiro->datos_bancarios ?? '';
        $this->comentario = $retiro->comentario ?? '';
    }

    protected function validateForm(): array
    {
        $validated = $this->validate(['empresa_id' => ['required', 'integer', 'exists:empresas,id'], 'monto' => ['required', 'decimal:0,2', 'min:0.01', 'max:9999999999.99'], 'datos_bancarios' => ['required', 'string', 'max:2000'], 'comentario' => ['nullable', 'string', 'max:2000']], [], ['empresa_id' => 'Empresa', 'monto' => 'Monto USD', 'datos_bancarios' => 'Datos bancarios del beneficiario', 'comentario' => 'Observaciones']);
        foreach ($validated as $key => &$value) {
            if ($value === '') {
                $value = null;
            }
        }
        unset($value);

        return $validated;
    }

    protected function findRetiro(): Retiro
    {
        return Retiro::searchAdmin()->with([0 => 'empresa', 1 => 'admin', 2 => 'revisor'])->findOrFail($this->retiro_id);
    }
}
