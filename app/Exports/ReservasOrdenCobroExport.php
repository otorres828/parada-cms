<?php

namespace App\Exports;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;

class ReservasOrdenCobroExport extends DefaultValueBinder implements FromCollection, WithCustomValueBinder, WithHeadings, WithMapping
{
    public function __construct(private array $reservas) {}

    public function collection(): Collection
    {
        return collect($this->reservas);
    }

    public function headings(): array
    {
        return [
            'ID reserva',
            'Código de referencia',
            'Fecha de pago',
            'Tasa de servicio',
        ];
    }

    public function map($reserva): array
    {
        return [
            $reserva['reserva_id'] ?? null,
            $reserva['codigo_referencia'] ?? null,
            ! empty($reserva['fecha_pago']) ? Carbon::parse($reserva['fecha_pago'])->format('d/m/Y H:i') : null,
            (float) ($reserva['tasa_servicio'] ?? 0),
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
