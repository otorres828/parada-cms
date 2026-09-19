<?php

namespace App\Livewire\Admin\Settings;

use App\Models\Configuracion;
use App\Services\Admin\Access;
use App\Services\Admin\Audit;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.cms')]
class WithdrawalSettings extends Component
{
    public $habilitados = true;

    public $minimo = '1.00';

    public $maximo = '10000.00';

    public function mount(): void
    {
        Access::authorize('settings', 'withdrawals');
        $values = Configuracion::where('grupo', 'withdrawals')->value('valores') ?? [];
        $this->habilitados = $values['habilitados'] ?? true;
        $this->minimo = $values['minimo'] ?? '1.00';
        $this->maximo = $values['maximo'] ?? '10000.00';
    }

    public function render()
    {
        Access::authorize('settings', 'withdrawals');

        return view('livewire.admin.settings.withdrawal-settings');
    }

    public function save(): void
    {
        Access::authorize('settings', 'withdrawals');
        $data = $this->validate(['habilitados' => 'required|boolean', 'minimo' => 'required|decimal:0,2|min:0.01|max:9999999999.99', 'maximo' => 'required|decimal:0,2|gte:minimo|max:9999999999.99']);
        DB::transaction(function () use ($data) {
            $record = Configuracion::updateOrCreate(['grupo' => 'withdrawals'], ['valores' => $data]);
            Audit::record('configuracion.actualizada', $record, $data);
        });
        $this->dispatch('successEventList', message: 'Configuración guardada.');
    }
}
