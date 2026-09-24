<?php

namespace App\Exports;

use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class SalesReportExport implements FromQuery, WithHeadings, WithMapping
{
    public function __construct(private Builder $consulta) {}

    public function query(): Builder
    {
        return $this->consulta;
    }

    public function headings(): array
    {
        return [
            'Fecha',
            'Reservas pagadas',
            'Ventas',
            'Tasas de servicio',
        ];
    }

    public function map($row): array
    {
        return [
            $row->fecha,
            (int) $row->cantidad,
            (float) $row->total,
            (float) $row->tasas,
        ];
    }
}
