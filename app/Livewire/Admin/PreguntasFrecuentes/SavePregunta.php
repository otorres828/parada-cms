<?php

namespace App\Livewire\Admin\PreguntasFrecuentes;

use App\Models\PreguntaFrecuente;
use App\Services\Admin\Access;
use App\Services\Admin\Audit;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;

#[Layout('layouts.cms')]
class SavePregunta extends Component
{
    #[Locked]
    public ?int $pregunta_id = null;

    public string $pregunta = '';

    public string $respuesta = '';

    public int $orden = 0;

    public int|string $estatus = PreguntaFrecuente::ESTADO_ACTIVE;

    public function mount(?int $pregunta_id = null): void
    {
        $this->pregunta_id = $pregunta_id;

        if ($this->pregunta_id) {
            $this->editar(
                PreguntaFrecuente::searchAdmin()->findOrFail($this->pregunta_id),
            );
        }
    }

    public function render()
    {
        return view('livewire.admin.preguntas-frecuentes.save');
    }

    public function save()
    {
        Access::authorize('preguntas-frecuentes', $this->pregunta_id ? 'edit' : 'add');

        $data = $this->validateForm();

        DB::transaction(function () use ($data) {
            $pregunta = $this->pregunta_id
                ? PreguntaFrecuente::query()->lockForUpdate()->findOrFail($this->pregunta_id)
                : new PreguntaFrecuente;

            $pregunta->fill($data);
            $pregunta->save();
            Audit::record($this->pregunta_id ? 'registro.actualizado' : 'registro.creado', $pregunta, $data);
        });

        session()->flash('admin_success', 'Pregunta guardada correctamente.');

        return $this->redirectRoute('admin.preguntas-frecuentes.list', navigate: true);
    }

    protected function editar(PreguntaFrecuente $pregunta): void
    {
        $this->pregunta = $pregunta->pregunta;
        $this->respuesta = $pregunta->respuesta;
        $this->orden = $pregunta->orden;
        $this->estatus = $pregunta->estatus;
    }

    protected function validateForm(): array
    {
        return $this->validate([
            'pregunta' => ['required', 'string', 'max:255'],
            'respuesta' => ['required', 'string', 'max:15000'],
            'orden' => ['required', 'integer', 'min:0', 'max:99999'],
            'estatus' => ['required', 'integer', 'in:1,2'],
        ], [], [
            'pregunta' => 'Pregunta',
            'respuesta' => 'Respuesta',
            'orden' => 'Orden',
            'estatus' => 'Estado',
        ]);
    }
}
