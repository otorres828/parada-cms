<?php

namespace App\Livewire\Admin\Settings;

use App\Models\Configuracion;
use App\Services\Admin\Access;
use App\Services\Admin\Audit;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.cms')]
class GeneralSettings extends Component
{
    public $nombre = 'Parada';

    public $email = '';

    public $telefono = '';

    public $moneda = 'USD';

    public function mount(): void
    {
        Access::authorize('settings', 'general');
        $values = Configuracion::where('grupo', 'general')->value('valores') ?? [];
        $this->nombre = $values['nombre'] ?? 'Parada';
        $this->email = $values['email'] ?? '';
        $this->telefono = $values['telefono'] ?? '';
        $this->moneda = $values['moneda'] ?? 'USD';
    }

    public function render()
    {
        Access::authorize('settings', 'general');

        return view('livewire.admin.settings.general-settings');
    }

    public function save(): void
    {
        Access::authorize('settings', 'general');
        $data = $this->validate(['nombre' => 'required|string|max:100', 'email' => 'required|email|max:255', 'telefono' => 'nullable|string|max:100', 'moneda' => 'required|in:USD']);
        DB::transaction(function () use ($data) {
            $record = Configuracion::updateOrCreate(['grupo' => 'general'], ['valores' => $data]);
            Audit::record('configuracion.actualizada', $record, $data);
        });
        $this->dispatch('successEventList', message: 'Configuración guardada.');
    }
}
