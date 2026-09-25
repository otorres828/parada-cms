<?php

namespace App\Livewire\Admin\Empresas;

use App\Models\Empresa;
use App\Services\Admin\Access;
use App\Services\Admin\Audit;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;

#[Layout('layouts.cms')]
class SaveEmpresa extends Component
{
    #[Locked]
    public ?int $empresa_id = null;

    public string $nombre = '';

    public string $rif = '';

    public string $telefono = '';

    public string $email = '';

    public int|string $tipo_contrato = Empresa::CONTRATO_ELLOS_RECIBEN;

    public int|string $dia_corte = '';

    public int|string $dia_vencimiento = '';

    public string $hora_corte = '00:00';

    public string $hora_vencimiento = '23:59';

    public int|string $estatus = 1;

    public Empresa $empresa;

    public function mount(?int $empresa_id = null): void
    {
        $this->empresa_id = $empresa_id;
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
            $empresa->tipo_contrato = $data['tipo_contrato'];
            $empresa->dia_corte = (int) $data['tipo_contrato'] === Empresa::CONTRATO_ELLOS_RECIBEN ? $data['dia_corte'] : null;
            $empresa->dia_vencimiento = (int) $data['tipo_contrato'] === Empresa::CONTRATO_ELLOS_RECIBEN ? $data['dia_vencimiento'] : null;
            $empresa->hora_corte = $data['hora_corte'];
            $empresa->hora_vencimiento = $data['hora_vencimiento'];

            if ($empresa->bloqueada_por_cobranza_at && (int) $data['estatus'] === Empresa::ESTADO_ACTIVE) {
                throw ValidationException::withMessages([
                    'estatus' => 'La empresa no puede activarse mientras tenga órdenes de cobro vencidas.',
                ]);
            }

            $empresa->estatus = $data['estatus'];
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
        $this->tipo_contrato = (string) $empresa->tipo_contrato;
        $this->dia_corte = (string) ($empresa->dia_corte ?? '');
        $this->dia_vencimiento = (string) ($empresa->dia_vencimiento ?? '');
        $this->hora_corte = substr((string) ($empresa->hora_corte ?? '00:00'), 0, 5);
        $this->hora_vencimiento = substr((string) ($empresa->hora_vencimiento ?? '23:59'), 0, 5);
        $this->estatus = (string) (is_bool($empresa->estatus) ? (int) $empresa->estatus : $empresa->estatus);
    }

    protected function validateForm(): array
    {
        $validated = $this->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'rif' => ['required', 'string', 'max:255'],
            'telefono' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'tipo_contrato' => ['required', 'integer', 'in:1,2'],
            'dia_corte' => ['nullable', 'required_if:tipo_contrato,1', 'integer', 'between:1,7'],
            'dia_vencimiento' => ['nullable', 'required_if:tipo_contrato,1', 'integer', 'between:1,7', 'different:dia_corte'],
            'hora_corte' => ['required', 'date_format:H:i'],
            'hora_vencimiento' => ['required', 'date_format:H:i'],
            'estatus' => ['required', 'in:0,1'],
        ], [], [
            'nombre' => 'Nombre',
            'rif' => 'Identificación fiscal',
            'telefono' => 'Teléfono',
            'email' => 'Correo',
            'tipo_contrato' => 'Tipo de contrato',
            'dia_corte' => 'Día de corte',
            'dia_vencimiento' => 'Día de cierre',
            'hora_corte' => 'Hora de corte',
            'hora_vencimiento' => 'Hora de cierre',
            'estatus' => 'Estado',
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
