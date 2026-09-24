<?php

namespace App\Livewire\Admin\TasasServicio;

use App\Models\GroupAdmin;
use App\Models\TasaServicio;
use App\Services\Admin\Access;
use App\Services\Admin\Audit;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;

#[Layout('layouts.cms')]
class SaveTasaServicio extends Component
{
    public TasaServicio $tasaServicio;

    #[Locked]
    public ?int $tasa_servicio_id = null;

    public string $monto_minimo = '';

    public string $monto_maximo = '';

    public string $cantidad = '';

    public $tipo_servicio = 1;

    public $estatus = 1;

    public function mount(?int $tasa_servicio_id = null): void
    {
        $this->tasa_servicio_id = $tasa_servicio_id;
        Access::authorize('tasas-servicio', $tasa_servicio_id ? 'edit' : 'add');
        if ($tasa_servicio_id) {
            $this->editar(TasaServicio::searchAdmin()->findOrFail($tasa_servicio_id));
        }
    }

    public function render()
    {
        return view('livewire.admin.tasas-servicio.save-tasa-servicio');
    }

    public function save()
    {
        Access::authorize('tasas-servicio', $this->tasa_servicio_id ? 'edit' : 'add');
        $data = $this->validate(['tipo_servicio' => 'required|in:1,2', 'monto_minimo' => 'required|decimal:0,2|min:0|max:9999999999.99', 'monto_maximo' => 'nullable|decimal:0,2|gte:monto_minimo|max:9999999999.99', 'cantidad' => 'required|decimal:0,2|min:0|max:'.((int) $this->tipo_servicio === 2 ? '100' : '9999999999.99'), 'estatus' => 'required|in:0,1']);
        $data['monto_maximo'] = $data['monto_maximo'] === '' ? null : $data['monto_maximo'];
        DB::transaction(function () use ($data) {
            GroupAdmin::where('url', 'administracion')->lockForUpdate()->firstOrFail();
            $tasa = $this->tasa_servicio_id ? TasaServicio::searchAdmin()->findOrFail($this->tasa_servicio_id) : new TasaServicio;
            $tasa->fill($data);
            $tasa->save();
            Audit::record('tasa_servicio.guardada', $tasa, $data);
        });
        session()->flash('admin_success', 'Tasa de servicio guardada.');

        return $this->redirect(route('admin.tasas-servicio.list'), navigate: true);
    }

    protected function editar(TasaServicio $tasa): void
    {
        $this->tasaServicio = $tasa;
        $this->monto_minimo = $tasa->monto_minimo;
        $this->monto_maximo = $tasa->monto_maximo ?? '';
        $this->cantidad = $tasa->cantidad;
        $this->tipo_servicio = $tasa->tipo_servicio;
        $this->estatus = (int) $tasa->estatus;
    }
}
