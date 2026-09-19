<?php

namespace App\Livewire\Admin\Reportes;

use App\Models\ModelHelper;
use App\Models\Movimiento;
use App\Services\Admin\Access;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.cms')]
class FinanceReport extends Component
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
        Access::authorize('reportes', 'finance');
        $this->date_from = $this->date_from ?: now()->startOfMonth()->toDateString();
        $this->date_to = $this->date_to ?: now()->toDateString();
    }

    public function render()
    {
        return view('livewire.admin.reportes.finance-report', ['rows' => $this->query()->paginate($this->per_page), 'columns' => $this->columns(), 'report' => 'finance']);
    }

    public function updated(): void
    {
        $this->resetPage();
    }

    protected function columns(): array
    {
        return ['fecha' => 'Fecha', 'tipo' => 'Concepto', 'cantidad' => 'Movimientos', 'total' => 'Importe neto USD'];
    }

    protected function query()
    {
        Access::authorize('reportes', 'finance');
        $filters = ['date_from' => $this->date_from, 'date_to' => $this->date_to];

        return Movimiento::searchAdmin('', $filters)->selectRaw('DATE(created_at) as fecha, tipo, COUNT(*) as cantidad, SUM(monto) as total')->groupByRaw('DATE(created_at), tipo')->orderByDesc('fecha');
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
        }, 'reporte-' . 'finance' . '-' . $this->date_from . '.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
