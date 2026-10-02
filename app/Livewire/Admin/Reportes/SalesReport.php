<?php

namespace App\Livewire\Admin\Reportes;

use App\Exports\SalesReportExport;
use App\Models\Reserva;
use App\Services\Admin\Access;
use App\Traits\TraitGeneral;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;

#[Layout('layouts.crm')]
class SalesReport extends Component
{
    use TraitGeneral;
    use WithPagination;

    protected string $paginationTheme = 'bootstrap';

    public string $tipo_transporte = '';

    public string $date_from = '';

    public string $date_to = '';

    public int $per_page = 25;

    protected array $queryString = [
        'tipo_transporte' => ['except' => ''],
        'date_from' => ['except' => ''],
        'date_to' => ['except' => ''],
        'per_page' => ['except' => 25],
    ];

    public function mount(): void
    {
        $this->date_from = $this->date_from ?: self::getDefaultDesde();
        $this->date_to = $this->date_to ?: self::getDefaultHasta();
    }

    public function render()
    {
        return view('livewire.admin.reportes.sales-report', [
            'rows' => $this->query()->paginate($this->per_page),
            'report' => 'sales',
        ]);
    }

    public function updated(): void
    {
        $this->resetPage();
    }

    protected function query()
    {
        return Reserva::salesReport($this->date_from, $this->date_to, $this->tipo_transporte);
    }

    public function export()
    {
        Access::authorize('reportes', 'list-sales');

        $this->validate([
            'tipo_transporte' => 'nullable|in:autobus,carro',
            'date_from' => 'required|date_format:Y-m-d|after_or_equal:'.self::getMinFilterDate().'|before_or_equal:'.self::getMaxFilterDate(),
            'date_to' => 'required|date_format:Y-m-d|after_or_equal:date_from|before_or_equal:'.self::getMaxFilterDate(),
            'per_page' => 'integer|in:10,25,50,100',
        ]);

        return Excel::download(
            new SalesReportExport($this->query()),
            'reporte-ventas-'.$this->date_from.'-'.$this->date_to.'.xlsx',
        );
    }
}
