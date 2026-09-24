<?php

namespace App\Livewire\Admin\Terminales;

use App\Models\Estado;
use App\Models\Terminal;
use App\Services\Admin\Access;
use App\Services\Admin\Audit;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;

#[Layout('layouts.cms')]
class SaveTerminal extends Component
{
    #[Locked]
    public ?int $terminal_id = null;

    public $estado_id = '';

    public string $search_estado_id = '';

    public $nombre = '';

    public $direccion = '';

    public $latitud = '';

    public $longitud = '';

    public $estatus = 1;

    public Terminal $terminal;

    public Collection $estados;

    public function mount(?int $terminal_id = null): void
    {
        $this->terminal_id = $terminal_id;
        $this->estados = Estado::searchAdmin()->orderBy('nombre')->get();
        if ($this->terminal_id) {
            $this->editar($this->findTerminal());
        }
    }

    public function render()
    {
        return view('livewire.admin.terminales.save-terminal');
    }

    public function save()
    {

        Access::authorize('terminales', $this->terminal_id ? 'edit' : 'add');
        $data = $this->validateForm();

        DB::transaction(function () use ($data) {

            Access::authorize('terminales', $this->terminal_id ? 'edit' : 'add');
            $terminal = $this->terminal_id ? $this->findTerminal() : new Terminal;
            $terminal->estado_id = $data['estado_id'];
            $terminal->nombre = $data['nombre'];
            $terminal->direccion = $data['direccion'];
            $terminal->latitud = $data['latitud'];
            $terminal->longitud = $data['longitud'];
            $terminal->estatus = $data['estatus'];
            $terminal->save();
            Audit::record($this->terminal_id ? 'registro.actualizado' : 'registro.creado', $terminal, $data);

            return $terminal;
        });

        session()->flash('admin_success', 'Registro guardado correctamente.');

        return $this->redirect(route('admin.terminales.list'), navigate: true);
    }

    protected function editar(Terminal $terminal): void
    {
        $this->terminal = $terminal;
        $this->estado_id = $terminal->estado_id ?? '';
        $this->nombre = $terminal->nombre ?? '';
        $this->direccion = $terminal->direccion ?? '';
        $this->latitud = $terminal->latitud ?? '';
        $this->longitud = $terminal->longitud ?? '';
        $this->estatus = (string) (is_bool($terminal->estatus) ? (int) $terminal->estatus : $terminal->estatus);
    }

    protected function validateForm(): array
    {
        $validated = $this->validate(['estado_id' => ['required', 'integer', 'exists:estados,id'], 'nombre' => ['required', 'string', 'max:255'], 'direccion' => ['required', 'string', 'max:2000'], 'latitud' => ['required', 'numeric', 'between:-90,90'], 'longitud' => ['required', 'numeric', 'between:-180,180'], 'estatus' => ['required', 'in:0,1']], [], ['estado_id' => 'Estado', 'nombre' => 'Nombre', 'direccion' => 'Dirección', 'latitud' => 'Latitud', 'longitud' => 'Longitud', 'estatus' => 'Estado']);
        foreach ($validated as $key => &$value) {
            if ($value === '') {
                $value = null;
            }
        }
        unset($value);

        return $validated;
    }

    protected function findTerminal(): Terminal
    {
        return Terminal::searchAdmin()->with([0 => 'estado'])->findOrFail($this->terminal_id);
    }
}
