<?php

namespace App\Livewire\Empresas\DatosBancarios;

use App\Livewire\Empresas\EmpresaComponent;
use App\Models\DatoBancario;
use App\Services\Empresa\Access;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;

#[Layout('layouts.crm')]
class SaveDatoBancario extends EmpresaComponent
{
    #[Locked]
    public ?int $cuentaId = null;

    public bool $canList = false;

    public array $datos = [
        'tipo' => 1,
        'banco' => '',
        'nombre_titular' => '',
        'tipo_titular' => 'personal',
        'numero_documento' => '',
        'numero_cuenta_telefono' => '',
        'tipo_cuenta' => null,
        'estatus' => 1,
    ];

    public function mount(?int $cuenta_id = null): void
    {
        $this->canList = $this->usuarioEmpresa->hasPermission('datos-bancarios', 'list');
        if ($cuenta_id !== null) {
            $cuenta = DatoBancario::where('empresa_id', $this->usuarioEmpresa->empresa_id)->findOrFail($cuenta_id);
            $this->cuentaId = $cuenta->id;
            $this->datos = $cuenta->only(array_keys($this->datos));
        }
    }

    public function save()
    {
        Access::authorize('datos-bancarios', $this->cuentaId === null ? 'add' : 'edit');
        $validated = $this->validate([
            'datos.tipo' => ['required', 'integer', 'in:1,2'],
            'datos.banco' => ['required', Rule::in(config('bancos'))],
            'datos.nombre_titular' => ['required', 'string', 'max:255'],
            'datos.tipo_titular' => ['required', 'in:juridico,extranjero,personal'],
            'datos.numero_documento' => ['required', 'string', 'max:30'],
            'datos.numero_cuenta_telefono' => (int) $this->datos['tipo'] === DatoBancario::PAGO_MOVIL
                ? ['required', 'regex:/^0(412|414|416|424|426)[0-9]{7}$/']
                : ['required', 'digits:20'],
            'datos.tipo_cuenta' => ['nullable', 'required_if:datos.tipo,2', 'in:corriente,ahorro'],
            'datos.estatus' => ['required', 'integer', 'in:1,2'],
        ], [
            'required' => 'El campo :attribute es obligatorio.',
            'required_if' => 'Selecciona el tipo de cuenta bancaria.',
            'integer' => 'Selecciona una opción válida.',
            'in' => 'Selecciona una opción válida para :attribute.',
            'string' => 'El campo :attribute debe ser texto.',
            'max' => 'El campo :attribute no debe superar :max caracteres.',
            'regex' => 'Ingresa un teléfono venezolano de 11 dígitos válido.',
            'digits' => 'La cuenta bancaria debe tener 20 dígitos.',
        ], [
            'datos.banco' => 'banco',
            'datos.nombre_titular' => 'nombre del titular',
            'datos.numero_documento' => 'documento del titular',
            'datos.numero_cuenta_telefono' => 'cuenta o teléfono',
        ]);
        $datos = $validated['datos'];

        $datos['tipo_cuenta'] = (int) $datos['tipo'] === DatoBancario::PAGO_MOVIL ? null : $datos['tipo_cuenta'];

        if ($this->cuentaId === null) {
            $cuenta = DatoBancario::create($datos + ['empresa_id' => $this->usuarioEmpresa->empresa_id]);
            $this->cuentaId = $cuenta->id;
        } else {
            DatoBancario::where('empresa_id', $this->usuarioEmpresa->empresa_id)->findOrFail($this->cuentaId)->update($datos);
        }
        
        $this->dispatch('successEventList', message: 'Datos bancarios guardados correctamente.');

        return $this->redirect(route('empresas.datos-bancarios.list'), navigate: true);


    }
}
