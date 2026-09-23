<?php

namespace App\Livewire\Admin\Empresas;

use App\Models\Empresa;
use App\Services\Admin\Access;
use App\Services\Admin\Audit;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;

#[Layout('layouts.cms')]
class SaveEmpresa extends Component
{
    #[Locked]
    public ?int $empresa_id = null;

    public $nombre = '';

    public $rif = '';

    public $telefono = '';

    public $email = '';

    public $estatus = 1;

    public $retiros_habilitados = 0;

    public $datos_bancarios = '';

    public Empresa $empresa;

    public function mount(?int $empresa_id = null): void
    {
        $this->empresa_id = $empresa_id;
        Access::authorize('empresas', $this->empresa_id ? 'edit' : 'add');
        if ($this->empresa_id) {
            $this->editar($this->findEmpresa());
        }
    }

    public function render()
    {
        return view('livewire.admin.empresas.save-empresa');
    }

    public function save()
    {

        Access::authorize('empresas', $this->empresa_id ? 'edit' : 'add');

        $data = $this->validateForm();

        $empresa = DB::transaction(function () use ($data) {

            Access::authorize('empresas', $this->empresa_id ? 'edit' : 'add');
            $empresa = $this->empresa_id ? $this->findEmpresa() : new Empresa;
            $empresa->nombre = $data['nombre'];
            $empresa->rif = $data['rif'];
            $empresa->telefono = $data['telefono'];
            $empresa->email = $data['email'];
            $empresa->estatus = $data['estatus'];
            $empresa->retiros_habilitados = $data['retiros_habilitados'];
            $empresa->datos_bancarios = $data['datos_bancarios'];
            $empresa->save();
            Audit::record($this->empresa_id ? 'registro.actualizado' : 'registro.creado', $empresa, $data);

            return $empresa;
        });

        session()->flash('admin_success', 'Registro guardado correctamente.');

        

        return $this->redirect(route('admin.empresas.list'), navigate: true);
    }

    protected function editar(Empresa $empresa): void
    {
        $this->empresa = $empresa;
        $this->nombre = $empresa->nombre ?? '';
        $this->rif = $empresa->rif ?? '';
        $this->telefono = $empresa->telefono ?? '';
        $this->email = $empresa->email ?? '';
        $this->retiros_habilitados = (string) (int) $empresa->retiros_habilitados;
        $this->datos_bancarios = $empresa->datos_bancarios ?? '';
        $this->estatus = (string) (is_bool($empresa->estatus) ? (int) $empresa->estatus : $empresa->estatus);
    }

    protected function validateForm(): array
    {
        $validated = $this->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'rif' => ['required', 'string', 'max:255'],
            'telefono' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'estatus' => ['required', 'in:0,1'],
            'retiros_habilitados' => ['required', 'in:0,1'],
            'datos_bancarios' => ['nullable', 'string', 'max:2000'],
        ], [], [
            'nombre' => 'Nombre',
            'rif' => 'Identificación fiscal',
            'telefono' => 'Teléfono',
            'email' => 'Correo',
            'estatus' => 'Estado',
            'retiros_habilitados' => 'Habilitar retiros',
            'datos_bancarios' => 'Datos bancarios',
        ]);
        foreach ($validated as $key => &$value) {
            if ($value === '') {
                $value = null;
            }
        }
        unset($value);

        return $validated;
    }

    protected function findEmpresa(): Empresa
    {
        return Empresa::findOrFail($this->empresa_id);
    }
}
