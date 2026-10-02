<?php

namespace App\Exports;

use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithCustomChunkSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class RoutesReportExport extends DefaultValueBinder implements WithCustomValueBinder, FromQuery, WithCustomChunkSize, WithHeadings, WithMapping
{
    public function __construct(private Builder $consulta) {}

    public function query(): Builder
    {
        return $this->consulta;
    }

    public function chunkSize(): int
    {
        return 1000;
    }

    public function bindValue(Cell $cell, mixed $value): bool
    {
        if (is_string($value)) {
            $cell->setValueExplicit($value, DataType::TYPE_STRING);

            return true;
        }

        return parent::bindValue($cell, $value);
    }

    public function headings(): array
    {
        return [
            'Origen',
            'Destino',
            'Reservas pagadas',
            'Ventas USD',
            'Ventas Bs',
            'Tasas de servicio USD',
            'Tasas de servicio Bs',
        ];
    }

    public function map($row): array
    {
        return [
            $row->origen,
            $row->destino,
            (int) $row->cantidad,
            (float) $row->total,
            (float) $row->total_bs,
            (float) $row->tasas,
            (float) $row->tasas_bs,
        ];
    }
}
