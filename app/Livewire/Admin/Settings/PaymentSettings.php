<?php

namespace App\Livewire\Admin\Settings;

use App\Models\Configuracion;
use App\Services\Admin\Access;
use App\Services\Admin\Audit;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.cms')]
class PaymentSettings extends Component
{
    public $transferencia = true;

    public $tarjeta = true;

    public $pasarela = true;

    public $efectivo = false;

    public function mount(): void
    {
        Access::authorize('settings', 'payments');
        $values = Configuracion::where('grupo', 'payments')->value('valores') ?? [];
        $this->transferencia = $values['transferencia'] ?? true;
        $this->tarjeta = $values['tarjeta'] ?? true;
        $this->pasarela = $values['pasarela'] ?? true;
        $this->efectivo = $values['efectivo'] ?? false;
    }

    public function render()
    {
        Access::authorize('settings', 'payments');

        return view('livewire.admin.settings.payment-settings');
    }

    public function save(): void
    {
        Access::authorize('settings', 'payments');
        $data = $this->validate(['transferencia' => 'required|boolean', 'tarjeta' => 'required|boolean', 'pasarela' => 'required|boolean', 'efectivo' => 'required|boolean']);
        DB::transaction(function () use ($data) {
            $record = Configuracion::updateOrCreate(['grupo' => 'payments'], ['valores' => $data]);
            Audit::record('configuracion.actualizada', $record, $data);
        });
        $this->dispatch('successEventList', message: 'Configuración guardada.');
    }
}
