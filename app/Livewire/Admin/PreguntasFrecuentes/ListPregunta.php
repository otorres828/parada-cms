<?php

namespace App\Livewire\Admin\PreguntasFrecuentes;

use App\Models\CategoriaPreguntaFrecuente;
use App\Models\PreguntaFrecuente;
use App\Services\Admin\Access;
use App\Services\Admin\Audit;
use App\Traits\Listing;
use App\Traits\Permissions;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.crm')]
class ListPregunta extends Component
{
    use Listing;
    use Permissions;
    use WithPagination;

    public string $categoria_id = '';

    public string $destacada = '';

    public string $estatus = '';

    public Collection $categorias;

    protected array $queryString = [
        'search' => ['except' => ''],
        'per_page' => ['except' => 10],
        'categoria_id' => ['except' => ''],
        'destacada' => ['except' => ''],
        'estatus' => ['except' => ''],
    ];

    public function mount(): void
    {
        $this->sortColumn = 'orden';
        $this->sortDirection = 'asc';
        $this->checkPermissions('preguntas-frecuentes');
        $this->categorias = CategoriaPreguntaFrecuente::searchAdmin()
            ->orderBy('orden')
            ->orderBy('nombre')
            ->get();
    }

    public function render()
    {
        $query = PreguntaFrecuente::searchAdmin($this->search, [
            'categoria_id' => $this->categoria_id,
            'destacada' => $this->destacada,
            'estatus' => $this->estatus,
        ]);

        $preguntas = $this->applySort($query)->paginate($this->per_page);

        return view('livewire.admin.preguntas-frecuentes.list', [
            'preguntas' => $preguntas,
        ]);
    }

    public function updated(string $property): void
    {
        if (in_array($property, ['search', 'categoria_id', 'destacada', 'estatus', 'per_page'], true)) {
            $this->resetPage();
        }
    }

    public function changeStatus(int $id): void
    {
        Access::authorize('preguntas-frecuentes', 'edit');

        DB::transaction(function () use ($id) {
            $pregunta = PreguntaFrecuente::query()->lockForUpdate()->findOrFail($id);
            $pregunta->estatus = (int) $pregunta->estatus === PreguntaFrecuente::ESTADO_ACTIVE
                ? PreguntaFrecuente::ESTADO_INACTIVE
                : PreguntaFrecuente::ESTADO_ACTIVE;
            $pregunta->save();
            Audit::record('registro.estado', $pregunta, ['estatus' => $pregunta->estatus]);
        });

        $this->dispatch('admin_pregunta_success', message: 'Estado actualizado.');
    }

    public function deletePregunta(int $id): void
    {
        Access::authorize('preguntas-frecuentes', 'delete');

        DB::transaction(function () use ($id) {
            $pregunta = PreguntaFrecuente::searchAdmin()
                ->whereKey($id)
                ->lockForUpdate()
                ->firstOrFail();
            Audit::record('registro.eliminado', $pregunta);
            $pregunta->estatus = PreguntaFrecuente::ESTADO_DELETE;
            $pregunta->save();
        });

        $this->dispatch('admin_pregunta_success', message: 'Pregunta eliminada.');
    }
}
