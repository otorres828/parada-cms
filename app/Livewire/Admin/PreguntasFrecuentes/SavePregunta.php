<?php

namespace App\Livewire\Admin\PreguntasFrecuentes;

use App\Models\CategoriaPreguntaFrecuente;
use App\Models\PreguntaFrecuente;
use App\Services\Admin\Access;
use App\Services\Admin\Audit;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;

#[Layout('layouts.crm')]
class SavePregunta extends Component
{
    #[Locked]
    public ?int $pregunta_id = null;

    public string $pregunta = '';

    public int|string $categoria_pregunta_frecuente_id = '';

    public string $slug = '';

    public string $resumen = '';

    public string $respuesta = '';

    public string $palabras_clave = '';

    public bool $destacada = false;

    public int $orden = 0;

    public int|string $estatus = PreguntaFrecuente::ESTADO_ACTIVE;

    public Collection $categorias;

    public function mount(?int $pregunta_id = null): void
    {
        $this->pregunta_id = $pregunta_id;
        $this->categorias = CategoriaPreguntaFrecuente::searchAdmin()
            ->orderBy('orden')
            ->orderBy('nombre')
            ->get();

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

        session()->flash('admin_pregunta_success', 'Pregunta guardada correctamente.');

        return $this->redirectRoute('admin.preguntas-frecuentes.list', navigate: true);
    }

    protected function editar(PreguntaFrecuente $pregunta): void
    {
        $this->categoria_pregunta_frecuente_id = $pregunta->categoria_pregunta_frecuente_id;
        $this->pregunta = $pregunta->pregunta;
        $this->slug = $pregunta->slug;
        $this->resumen = $pregunta->resumen;
        $this->respuesta = $pregunta->respuesta;
        $this->palabras_clave = $pregunta->palabras_clave ?? '';
        $this->destacada = $pregunta->destacada;
        $this->orden = $pregunta->orden;
        $this->estatus = $pregunta->estatus;
    }

    protected function validateForm(): array
    {
        $this->slug = Str::slug($this->slug ?: $this->pregunta);

        return $this->validate([
            'categoria_pregunta_frecuente_id' => [
                'required',
                'integer',
                Rule::exists('categorias_preguntas_frecuentes', 'id')->where(function ($query) {
                    $query->where('estatus', '!=', CategoriaPreguntaFrecuente::ESTADO_DELETE);
                }),
            ],
            'pregunta' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'string',
                'max:255',
                Rule::unique('preguntas_frecuentes', 'slug')->ignore($this->pregunta_id),
            ],
            'resumen' => ['required', 'string', 'max:500'],
            'respuesta' => ['required', 'string', 'max:100000'],
            'palabras_clave' => ['nullable', 'string', 'max:1000'],
            'destacada' => ['required', 'boolean'],
            'orden' => ['required', 'integer', 'min:0', 'max:99999'],
            'estatus' => ['required', 'integer', 'in:1,2'],
        ], [], [
            'categoria_pregunta_frecuente_id' => 'Categoría',
            'pregunta' => 'Pregunta',
            'slug' => 'Slug',
            'resumen' => 'Resumen',
            'respuesta' => 'Respuesta',
            'palabras_clave' => 'Palabras clave',
            'destacada' => 'Destacada',
            'orden' => 'Orden',
            'estatus' => 'Estado',
        ]);
    }
}
