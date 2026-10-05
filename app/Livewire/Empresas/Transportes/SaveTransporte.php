<?php

namespace App\Livewire\Empresas\Transportes;

use App\Livewire\Empresas\EmpresaComponent;
use App\Models\Amenidad;
use App\Models\Transporte;
use App\Services\Empresa\Access;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;

#[Layout('layouts.crm')]
class SaveTransporte extends EmpresaComponent
{
    #[Locked]
    public ?int $transporteId = null;

    #[Locked]
    public Collection $amenidades;

    public bool $canList = false;

    public string $placa = '';

    public string $modelo = '';

    public string $tipo_asiento = '';

    public int $total_asientos = 4;

    public int $estatus = 1;

    public array $amenidadesSeleccionadas = [];

    public function mount(?int $transporte_id = null): void
    {
        $this->canList = $this->usuarioEmpresa->hasPermission('transportes', 'list');
        $this->total_asientos = $this->usuarioEmpresa->empresa->getTipoTransporte() === Transporte::CARRO ? 4 : 40;
        $this->amenidades = Amenidad::searchAdmin('', ['status' => Amenidad::ESTADO_ACTIVE])->orderBy('nombre')->get();

        if ($transporte_id !== null) {
            $transporte = Transporte::searchAdmin('', [
                'empresa_id' => $this->usuarioEmpresa->empresa_id,
                'tipo_transporte' => $this->usuarioEmpresa->empresa->getTipoTransporte(),
            ])->where('es_plantilla', false)->findOrFail($transporte_id);
            $this->transporteId = $transporte->id;
            $this->placa = $transporte->placa ?? '';
            $this->modelo = $transporte->modelo;
            $this->tipo_asiento = $transporte->tipo_asiento;
            $this->total_asientos = $transporte->total_asientos;
            $this->estatus = $transporte->estatus;
            $this->amenidadesSeleccionadas = $transporte->amenidades->where('estatus', Amenidad::ESTADO_ACTIVE)->pluck('id')->all();
        }
    }

    public function render()
    {
        return view('livewire.empresas.transportes.save-transporte');
    }

    public function save(): void
    {
        Access::authorize('transportes', $this->transporteId === null ? 'add' : 'edit');
        $this->placa = mb_strtoupper(trim($this->placa));
        $this->modelo = trim($this->modelo);
        $this->tipo_asiento = trim($this->tipo_asiento);
        $datos = $this->validate([
            'placa' => ['required', 'string', 'max:255', Rule::unique('transportes', 'placa')->ignore($this->transporteId)],
            'modelo' => ['required', 'string', 'max:255'],
            'tipo_asiento' => ['required', 'string', 'max:255'],
            'total_asientos' => ['required', 'integer', 'between:1,100'],
            'estatus' => ['required', 'integer', 'in:1,2'],
            'amenidadesSeleccionadas' => ['array'],
            'amenidadesSeleccionadas.*' => ['integer', 'distinct', Rule::exists('amenidades', 'id')->where('estatus', Amenidad::ESTADO_ACTIVE)],
        ], [
            'required' => 'El campo :attribute es obligatorio.',
            'string' => 'El campo :attribute debe ser texto.',
            'max' => 'El campo :attribute no puede superar :max caracteres.',
            'unique' => 'Esta placa ya está registrada.',
            'integer' => 'El campo :attribute debe ser un número entero.',
            'between' => 'La capacidad debe estar entre 1 y 100 puestos.',
            'in' => 'El estatus seleccionado no es válido.',
            'array' => 'Selecciona amenidades válidas.',
            'distinct' => 'No repitas amenidades.',
            'exists' => 'Una amenidad seleccionada ya no está disponible.',
        ], [
            'tipo_asiento' => 'tipo de asiento',
            'total_asientos' => 'cantidad de puestos',
            'amenidadesSeleccionadas.*' => 'amenidad',
        ]);

        DB::transaction(function () use ($datos) {
            $transporte = $this->transporteId === null ? new Transporte : Transporte::searchAdmin('', [
                'empresa_id' => $this->usuarioEmpresa->empresa_id,
                'tipo_transporte' => $this->usuarioEmpresa->empresa->getTipoTransporte(),
            ])->where('es_plantilla', false)->lockForUpdate()->findOrFail($this->transporteId);
            $transporte->fill([
                'empresa_id' => $this->usuarioEmpresa->empresa_id,
                'tipo_transporte' => $this->usuarioEmpresa->empresa->getTipoTransporte(),
                'placa' => $datos['placa'],
                'modelo' => $datos['modelo'],
                'tipo_asiento' => $datos['tipo_asiento'],
                'total_asientos' => $datos['total_asientos'],
                'es_plantilla' => false,
                'estatus' => $datos['estatus'],
            ])->save();
            $transporte->amenidades()->sync($datos['amenidadesSeleccionadas']);
        });

        if ($this->canList) {
            session()->flash('empresas_transporte_success', 'Transporte guardado correctamente.');
            $this->redirectRoute('empresas.transportes.list', navigate: true);
        } else {
            if ($this->transporteId === null) {
                $this->reset('placa', 'modelo', 'tipo_asiento', 'amenidadesSeleccionadas');
            }
            $this->dispatch('empresas_transporte_success', message: 'Transporte guardado correctamente.');
        }
    }


}
