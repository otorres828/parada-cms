<?php

namespace App\Livewire\Admin\ExoneracionesTasaServicio;

use App\Models\Empresa;
use App\Models\ExoneracionTasaServicio;
use App\Services\Admin\Access;
use App\Services\Admin\Audit;
use App\Traits\Listing;
use App\Traits\Permissions;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.cms')]
class ListExoneracionTasaServicio extends Component
{
    use Listing, Permissions, WithPagination;

    public string $empresa_id = '';

    public string $status = '';

    protected array $queryString = [
        'search' => ['except' => ''],
        'empresa_id' => ['except' => ''],
        'status' => ['except' => ''],
        'per_page' => ['except' => 10],
    ];

    public function mount(): void
    {
        $this->sortColumn = 'fecha_desde';
        $this->sortDirection = 'desc';
        $this->checkPermissions('exoneraciones-tasa-servicio');
    }

    public function render()
    {
        $query = ExoneracionTasaServicio::searchAdmin($this->search, [
            'empresa_id' => $this->empresa_id,
            'status' => $this->status,
        ]);

        return view('livewire.admin.exoneraciones-tasa-servicio.list-exoneracion-tasa-servicio', [
            'exoneraciones' => $this->applySort($query)->paginate($this->per_page),
            'empresas' => Empresa::searchAdmin('', ['status' => 1])->orderBy('nombre')->get(),
        ]);
    }

    public function updated(string $property): void
    {
        if (in_array($property, ['search', 'empresa_id', 'status', 'per_page'], true)) {
            $this->resetPage();
        }
    }

    public function changeStatus(int $id): void
    {
        Access::authorize('exoneraciones-tasa-servicio', 'edit');

        try {
            DB::transaction(function () use ($id) {
                $exoneracion = ExoneracionTasaServicio::query()->lockForUpdate()->findOrFail($id);
                $exoneracion->estatus = $exoneracion->estatus === ExoneracionTasaServicio::ACTIVO
                    ? ExoneracionTasaServicio::INACTIVO
                    : ExoneracionTasaServicio::ACTIVO;
                $exoneracion->validarSolapamiento();
                $exoneracion->save();
                Audit::record('exoneracion_tasa.estado', $exoneracion, ['estatus' => $exoneracion->estatus]);
            });
        } catch (ValidationException $exception) {
            $this->dispatch('errorEventList', message: collect($exception->errors())->flatten()->first());

            return;
        }

        $this->dispatch('successEventList', message: 'Estado actualizado.');
    }

    public function deleteExoneracion(int $id): void
    {
        Access::authorize('exoneraciones-tasa-servicio', 'delete');

        DB::transaction(function () use ($id) {
            $exoneracion = ExoneracionTasaServicio::query()->lockForUpdate()->findOrFail($id);
            $exoneracion->estatus = ExoneracionTasaServicio::ELIMINADO;
            $exoneracion->save();
            Audit::record('exoneracion_tasa.eliminada', $exoneracion, ['estatus' => $exoneracion->estatus]);
        });

        $this->dispatch('successEventList', message: 'Exoneración eliminada.');
    }
}
