<?php

namespace App\Livewire\Admin\Reportes;

use App\Models\ModelHelper;
use App\Models\Reserva;
use App\Services\Admin\Access;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.cms')]
class SalesReport extends Component
{
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
        Access::authorize('reportes', 'sales');
        $this->date_from = $this->date_from ?: now()->startOfMonth()->toDateString();
        $this->date_to = $this->date_to ?: now()->toDateString();
    }

    public function render()
    {
        return view('livewire.admin.reportes.sales-report', ['rows' => $this->query()->paginate($this->per_page), 'columns' => $this->columns(), 'report' => 'sales']);
    }

    public function updated(): void
    {
        $this->resetPage();
    }

    protected function columns(): array
    {
        return ['fecha' => 'Fecha', 'cantidad' => 'Reservas pagadas', 'total' => 'Ventas USD', 'tasas' => 'Tasas USD'];
    }

    protected function query()
    {
        Access::authorize('reportes', 'sales');
        $filters = ['date_from' => $this->date_from, 'date_to' => $this->date_to];

        return Reserva::searchAdmin('', $filters + ['estado_pago' => 'pagado'])->selectRaw('DATE(fecha_compra) as fecha, COUNT(*) as cantidad, SUM(monto_total) as total, SUM(tasa_servicio) as tasas')->groupByRaw('DATE(fecha_compra)')->orderByDesc('fecha');
    }

    public function export()
    {
        $this->validate(['date_from' => 'required|date_format:Y-m-d', 'date_to' => 'required|date_format:Y-m-d|after_or_equal:date_from', 'per_page' => 'integer|in:10,25,50,100']);
        $query = $this->query();
        $columns = $this->columns();

        return response()->streamDownload(function () use ($query, $columns) {
            $out = fopen('php://output', 'w');
            fwrite($out, '﻿');
            fputcsv($out, array_values($columns), ';', '"', '');

            foreach ($query->cursor() as $row) {
                $values = [];

                foreach (array_keys($columns) as $key) {
                    $value = ModelHelper::value($row, $key);

                    if (preg_match('/^[=+\-@\t\r]/', $value)) {
                        $value = "'" . $value;
                    }
                    $values[] = $value;
                }
                fputcsv($out, $values, ';', '"', '');
            }
            fclose($out);
        }, 'reporte-' . 'sales' . '-' . $this->date_from . '.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
