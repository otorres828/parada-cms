<?php

namespace App\Livewire\Empresas\PoliticasEmbarque;

use App\Livewire\Empresas\EmpresaComponent;
use App\Models\Empresa;
use App\Services\Empresa\Access;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;

#[Layout('layouts.crm')]
class SavePoliticaEmbarque extends EmpresaComponent
{
    public string $contenido = '';

    public function mount(): void
    {
        $this->contenido = Empresa::findOrFail($this->usuarioEmpresa->empresa_id)->politicas ?? '';
    }

    public function render(): View
    {
        return view('livewire.empresas.politicas-embarque.save-politica-embarque');
    }

    public function save(): void
    {
        Access::authorize('politicas-embarque', 'edit');
        $this->validate([
            'contenido' => ['required', 'string', 'max:60000'],
        ], [
            'required' => 'Ingresa las políticas de embarque y desembarque.',
            'string' => 'El contenido debe ser texto.',
            'max' => 'Las políticas no pueden superar 60000 caracteres.',
        ]);

        Empresa::findOrFail($this->usuarioEmpresa->empresa_id)->update([
            'politicas' => $this->contenido,
        ]);
        $this->dispatch('empresas_politica_embarque_success', message: 'Políticas de embarque y desembarque guardadas correctamente.');
    }
}
