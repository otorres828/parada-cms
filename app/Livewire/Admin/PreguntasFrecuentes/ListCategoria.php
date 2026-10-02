<?php

namespace App\Livewire\Admin\PreguntasFrecuentes;

use App\Models\CategoriaPreguntaFrecuente;
use App\Services\Admin\Access;
use App\Services\Admin\Audit;
use App\Traits\Listing;
use App\Traits\Permissions;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.crm')]
class ListCategoria extends Component
{
    use Listing;
    use Permissions;
    use WithPagination;

    public string $estatus = '';

    protected array $queryString = [
        'search' => ['except' => ''],
        'per_page' => ['except' => 10],
        'estatus' => ['except' => ''],
    ];

    public function mount(): void
    {
        $this->sortColumn = 'orden';
        $this->sortDirection = 'asc';
        $this->checkPermissions('preguntas-frecuentes');
    }

    public function render()
    {
        $query = CategoriaPreguntaFrecuente::searchAdmin($this->search, [
            'estatus' => $this->estatus,
        ]);

        return view('livewire.admin.preguntas-frecuentes.list-categoria', [
            'categorias' => $this->applySort($query)->paginate($this->per_page),
        ]);
    }

    public function updated(string $property): void
    {
        if (in_array($property, ['search', 'estatus', 'per_page'], true)) {
            $this->resetPage();
        }
    }

    public function changeStatus(int $id): void
    {
        Access::authorize('preguntas-frecuentes', 'edit');

        DB::transaction(function () use ($id) {
            $categoria = CategoriaPreguntaFrecuente::query()->lockForUpdate()->findOrFail($id);
            $categoria->estatus = (int) $categoria->estatus === CategoriaPreguntaFrecuente::ESTADO_ACTIVE
                ? CategoriaPreguntaFrecuente::ESTADO_INACTIVE
                : CategoriaPreguntaFrecuente::ESTADO_ACTIVE;
            $categoria->save();

            Audit::record('registro.estado', $categoria, ['estatus' => $categoria->estatus]);
        });

        $this->dispatch('successEventList', message: 'Estado actualizado.');
    }

    public function deleteCategoria(int $id): void
    {
        Access::authorize('preguntas-frecuentes', 'delete');

        $categoria = CategoriaPreguntaFrecuente::searchAdmin()->whereKey($id)->firstOrFail();

        if ($categoria->preguntas()->where('estatus', '!=', CategoriaPreguntaFrecuente::ESTADO_DELETE)->exists()) {
            $this->dispatch('errorEventList', message: 'No se puede eliminar una categoría que contiene preguntas.');

            return;
        }

        DB::transaction(function () use ($categoria) {
            Audit::record('registro.eliminado', $categoria);
            $categoria->estatus = CategoriaPreguntaFrecuente::ESTADO_DELETE;
            $categoria->save();
        });

        $this->dispatch('successEventList', message: 'Categoría eliminada.');
    }
}
