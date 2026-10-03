<?php

namespace App\Livewire\Empresas\Reportes;

use App\Exports\RoutesReportExport;
use App\Livewire\Empresas\EmpresaComponent;
use App\Models\Reserva;
use App\Services\Empresa\Access;
use App\Traits\TraitGeneral;
use App\Traits\PermissionsEmpresa;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;

#[Layout('layouts.crm')]
class RoutesReport extends EmpresaComponent
{
    use TraitGeneral;
    use PermissionsEmpresa;
    use WithPagination;

    protected string $paginationTheme = 'bootstrap';

    public string $date_from = '';

    public string $date_to = '';

    public int $per_page = 25;

    protected array $queryString = [
        'date_from' => ['except' => ''],
        'date_to' => ['except' => ''],
        'per_page' => ['except' => 25],
    ];

    public function mount(): void
    {
        $this->checkPermissions('reporte-rutas');
        $this->date_from = $this->date_from ?: self::getDefaultDesde();
        $this->date_to = $this->date_to ?: self::getDefaultHasta();
    }

    public function render()
    {
        return view('livewire.empresas.reportes.routes-report', [
            'rows' => $this->query()->paginate(max(1, min(100, $this->per_page))),
            'report' => 'routes',
        ]);
    }

    public function updated(): void
    {
        $this->resetPage();
    }

    protected function query()
    {
        return Reserva::routesReport($this->date_from, $this->date_to, $this->usuarioEmpresa->empresa_id, $this->usuarioEmpresa->empresa->getTipoTransporte());
    }

    public function export()
    {
        Access::authorize('reporte-rutas', 'download');

        $this->validate([
            'date_from' => 'required|date_format:Y-m-d|after_or_equal:'.self::getMinFilterDate().'|before_or_equal:'.self::getMaxFilterDate(),
            'date_to' => 'required|date_format:Y-m-d|after_or_equal:date_from|before_or_equal:'.self::getMaxFilterDate(),
            'per_page' => 'integer|in:10,25,50,100',
        ]);

        return Excel::download(
            new RoutesReportExport(
                $this->query(),
                $this->viewTasaServicio
            ),
            'reporte-rutas-'.$this->date_from.'-'.$this->date_to.'.xlsx',
        );
    }
}
