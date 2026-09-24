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

class ReembolsosExport extends DefaultValueBinder implements FromQuery, WithCustomValueBinder, WithHeadings, WithMapping
{
    public function __construct(private Builder $consulta) {}

    public function query(): Builder
    {
        return $this->consulta;
    }

    public function headings(): array
    {
        return [
            'ID reembolso',
            'Reserva',
            'Referencia de pago',
            'Empresa',
            'Monto',
            'Moneda',
            'Estado',
            'Motivo',
            'Observaciones',
            'Referencia del reembolso',
            'Solicitado por',
            'Revisado por',
            'Fecha de solicitud',
            'Fecha de resolución',
        ];
    }

    public function map($reembolso): array
    {
        return [
            $reembolso->id,
            $reembolso->pagoReserva?->reserva?->codigo_referencia,
            $reembolso->pagoReserva?->referencia_pago,
            $reembolso->empresa?->nombre,
            (float) $reembolso->monto,
            $reembolso->moneda,
            ucfirst($reembolso->estatus),
            $reembolso->motivo,
            $reembolso->comentario,
            $reembolso->referencia,
            $reembolso->admin?->name,
            $reembolso->revisor?->name,
            $reembolso->created_at?->format('d/m/Y H:i'),
            $reembolso->fecha_resolucion?->format('d/m/Y H:i'),
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
