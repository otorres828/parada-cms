<?php

namespace App\Livewire\Empresas\Viajes;

use App\Livewire\Empresas\EmpresaComponent;
use App\Models\Terminal;
use App\Models\Viaje;
use App\Services\Empresa\Access;
use App\Services\Empresa\ViajeService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;

#[Layout('layouts.crm')]
class SaveViaje extends EmpresaComponent
{
    #[Locked]
    public ?int $viajeId = null;

    public bool $canList = false;

    public string $origenId = '';

    public string $terminalId = '';

    public array $paradas = [];

    public array $precios = [];

    public array $minutos = [];

    public string $comentario = '';

    public int $estatus = 1;

    public function mount(?int $viaje_id = null): void
    {
        $this->canList = $this->usuarioEmpresa->hasPermission('viajes', 'list');
        if ($viaje_id !== null) {
            $viaje = Viaje::where('empresa_id', $this->usuarioEmpresa->empresa_id)->with('tramos')->findOrFail($viaje_id);
            $this->viajeId = $viaje->id;
            $this->origenId = (string) $viaje->origen_terminal_id;
            $this->paradas = $viaje->secuenciaTerminales();
            $this->comentario = $viaje->comentario ?? '';
            $this->estatus = (int) $viaje->estatus;
            foreach ($viaje->tramos as $tramo) {
                $clave = $tramo->origen_terminal_id.'-'.$tramo->destino_terminal_id;
                $this->precios[$clave] = $tramo->precio ?? '';
                if ($tramo->posicion_destino === $tramo->posicion_origen + 1) {
                    [$horas, $minutos] = explode(':', $tramo->duracion_estimada ?? '00:00:00');
                    $this->minutos[$clave] = (int) $horas * 60 + (int) $minutos;
                }
            }
            $this->sincronizarTramos();
        }
    }

    public function render(): View
    {
        return view('livewire.empresas.viajes.save-viaje', [
            'terminales' => Terminal::searchAdmin('', ['status' => Terminal::ESTADO_ACTIVE])->orderBy('nombre')->get()->keyBy('id'),
            'combinaciones' => Viaje::combinaciones($this->paradas),
        ]);
    }

    public function updatedOrigenId(): void
    {
        if ($this->viajeId !== null) {
            return;
        }
        $destinos = array_slice($this->paradas, 1);
        $this->paradas = $this->origenId === '' ? [] : array_merge([(int) $this->origenId], array_values(array_filter($destinos, function ($id) {
            return (int) $id !== (int) $this->origenId;
        })));
        $this->sincronizarTramos();
    }

    public function agregarParada(): void
    {
        $this->validate([
            'terminalId' => ['required', 'integer', 'exists:terminales,id'],
        ], [
            'required' => 'Selecciona el destino o parada.',
            'integer' => 'Selecciona un terminal válido.',
            'exists' => 'El terminal seleccionado no existe.',
        ]);
        Viaje::exigir(count($this->paradas) > 0, 'origenId', 'Selecciona primero el origen.');
        Viaje::exigir(count($this->paradas) < 20, 'terminalId', 'El recorrido admite hasta 20 terminales.');
        Viaje::exigir(! in_array((int) $this->terminalId, array_map('intval', $this->paradas), true), 'terminalId', 'Este terminal ya está en el recorrido.');
        if ($this->viajeId !== null) {
            array_splice($this->paradas, count($this->paradas) - 1, 0, [(int) $this->terminalId]);
        } else {
            $this->paradas[] = (int) $this->terminalId;
        }
        $this->terminalId = '';
        $this->resetValidation();
        $this->sincronizarTramos();
    }

    public function removerParada(int $indice): void
    {
        Viaje::exigir($this->viajeId === null, 'paradas', 'No puedes eliminar paradas ni tramos al editar una ruta.');
        Viaje::exigir($indice > 0 && isset($this->paradas[$indice]), 'paradas', 'No puedes retirar el origen del recorrido.');
        array_splice($this->paradas, $indice, 1);
        $this->sincronizarTramos();
    }

    private function sincronizarTramos(): void
    {
        $precios = [];
        $minutos = [];
        foreach (Viaje::combinaciones($this->paradas) as $tramo) {
            $clave = $tramo['clave'];
            $precios[$clave] = $this->precios[$clave] ?? '';
            if ($tramo['posicion_destino'] === $tramo['posicion_origen'] + 1) {
                $minutos[$clave] = $this->minutos[$clave] ?? '';
            }
        }
        $this->precios = $precios;
        $this->minutos = $minutos;
    }

    public function save(): mixed
    {
        Access::authorize('viajes', $this->viajeId === null ? 'add' : 'edit');
        ViajeService::guardar($this->usuarioEmpresa, $this->viajeId, $this->paradas, $this->precios, $this->minutos, $this->comentario, $this->estatus);
        session()->flash('empresa_success', 'Ruta de viaje guardada correctamente.');

        if ($this->canList) {
            return $this->redirect(route('empresas.viajes.list'), navigate: true);
        }
        $this->dispatch('successEventList', message: 'Ruta de viaje guardada correctamente.');
        if ($this->viajeId === null) {
            $this->reset('origenId', 'terminalId', 'paradas', 'precios', 'minutos', 'comentario', 'estatus');
        }

        return null;
    }
}
