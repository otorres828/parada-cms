<?php

namespace App\Exports;

use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;

class CompaniesReportExport extends DefaultValueBinder implements FromQuery, WithCustomValueBinder, WithHeadings, WithMapping
{
    public function __construct(private Builder $consulta) {}

    public function query(): Builder
    {
        return $this->consulta;
    }

    public function headings(): array
    {
        return [
            'Empresa',
            'Reservas pagadas',
            'Ventas',
            'Tasas de servicio',
        ];
    }

    public function map($row): array
    {
        return [
            $row->nombre,
            (int) $row->cantidad,
            (float) $row->total,
            (float) $row->tasas,
        ];
    }

    public function bindValue(Cell $cell, mixed $value): bool
    {
        if (is_string($value)) {
            $cell->setValueExplicit($value, DataType::TYPE_STRING);

            return true;
        }

        return parent::bindValue($cell, $value);
    }
}
