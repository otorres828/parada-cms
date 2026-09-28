<?php

namespace App\Livewire\Admin\PreguntasFrecuentes;

use App\Models\CategoriaPreguntaFrecuente;
use App\Services\Admin\Access;
use App\Services\Admin\Audit;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

#[Layout('layouts.cms')]
class SaveCategoria extends Component
{
    use WithFileUploads;

    #[Locked]
    public ?int $categoria_id = null;

    public string $nombre = '';

    public string $slug = '';

    public string $descripcion = '';

    public string $icono = '';

    public ?TemporaryUploadedFile $imagen = null;

    public ?string $imagen_actual = null;

    public bool $destacada = false;

    public int $orden = 0;

    public int|string $estatus = CategoriaPreguntaFrecuente::ESTADO_ACTIVE;

    public function mount(?int $categoria_id = null): void
    {
        $this->categoria_id = $categoria_id;

        if ($this->categoria_id) {
            $this->editar(
                CategoriaPreguntaFrecuente::searchAdmin()->findOrFail($this->categoria_id),
            );
        }
    }

    public function render()
    {
        return view('livewire.admin.preguntas-frecuentes.save-categoria');
    }

    public function save()
    {
        Access::authorize('preguntas-frecuentes', $this->categoria_id ? 'edit' : 'add');

        $data = $this->validateForm();
        $imagenAnterior = $this->imagen_actual;

        if ($this->imagen) {
            $data['imagen'] = $this->imagen->store('centro-ayuda/categorias', 'public');
        }

        DB::transaction(function () use ($data) {
            $categoria = $this->categoria_id
                ? CategoriaPreguntaFrecuente::query()->lockForUpdate()->findOrFail($this->categoria_id)
                : new CategoriaPreguntaFrecuente;

            $categoria->fill($data);
            $categoria->save();

            Audit::record($this->categoria_id ? 'registro.actualizado' : 'registro.creado', $categoria, $data);
        });

        if ($this->imagen && $imagenAnterior) {
            Storage::disk('public')->delete($imagenAnterior);
        }

        session()->flash('admin_success', 'Categoría guardada correctamente.');

        return $this->redirectRoute('admin.preguntas-frecuentes.categorias.list', navigate: true);
    }

    protected function editar(CategoriaPreguntaFrecuente $categoria): void
    {
        $this->nombre = $categoria->nombre;
        $this->slug = $categoria->slug;
        $this->descripcion = $categoria->descripcion ?? '';
        $this->icono = $categoria->icono ?? '';
        $this->imagen_actual = $categoria->imagen;
        $this->destacada = $categoria->destacada;
        $this->orden = $categoria->orden;
        $this->estatus = $categoria->estatus;
    }

    protected function validateForm(): array
    {
        $this->slug = Str::slug($this->slug ?: $this->nombre);

        return $this->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'string',
                'max:255',
                Rule::unique('categorias_preguntas_frecuentes', 'slug')->ignore($this->categoria_id),
            ],
            'descripcion' => ['nullable', 'string', 'max:500'],
            'icono' => ['nullable', 'string', 'max:100'],
            'imagen' => ['nullable', 'image', 'max:3072'],
            'destacada' => ['required', 'boolean'],
            'orden' => ['required', 'integer', 'min:0', 'max:99999'],
            'estatus' => ['required', 'integer', 'in:1,2'],
        ], [], [
            'nombre' => 'Nombre',
            'slug' => 'Slug',
            'descripcion' => 'Descripción',
            'icono' => 'Icono',
            'imagen' => 'Imagen',
            'destacada' => 'Destacada',
            'orden' => 'Orden',
            'estatus' => 'Estado',
        ]);
    }
}
