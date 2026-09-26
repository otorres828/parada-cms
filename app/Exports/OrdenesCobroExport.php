<?php

namespace App\Exports;

use App\Support\ConversorMoneda;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;

class OrdenesCobroExport extends DefaultValueBinder implements FromQuery, WithCustomValueBinder, WithHeadings, WithMapping
{
    private array $conversionesBs = [];

    public function __construct(private Builder $consulta) {}

    public function query(): Builder
    {
        return $this->consulta;
    }

    public function headings(): array
    {
        return [
            'ID',
            'Código',
            'Empresa',
            'Identificación de la empresa',
            'Período desde',
            'Período hasta',
            'Fecha de emisión',
            'Fecha de vencimiento',
            'Cantidad de reservas',
            'Total USD',
            'Total Bs',
            'Estado',
            'Referencia de pago',
            'Fecha de pago reportado',
            'Fecha de aprobación',
            'Revisado por',
            'Comentarios',
        ];
    }

    public function map($ordenCobro): array
    {
        $this->conversionesBs[$ordenCobro->id] ??= ConversorMoneda::ordenes([$ordenCobro])[$ordenCobro->id];

        return [
            $ordenCobro->id,
            $ordenCobro->codigo,
            $ordenCobro->empresa?->nombre,
            $ordenCobro->empresa?->identificacion,
            $ordenCobro->periodo_desde?->format('d/m/Y H:i'),
            $ordenCobro->periodo_hasta?->format('d/m/Y H:i'),
            $ordenCobro->fecha_emision?->format('d/m/Y H:i'),
            $ordenCobro->fecha_vencimiento?->format('d/m/Y H:i'),
            $ordenCobro->cantidad_reservas,
            (float) $ordenCobro->total,
            (float) $this->conversionesBs[$ordenCobro->id]['total_bs'],
            $ordenCobro->getEstatusNombre(),
            $ordenCobro->referencia_pago,
            $ordenCobro->fecha_pago_reportado?->format('d/m/Y H:i'),
            $ordenCobro->fecha_aprobacion?->format('d/m/Y H:i'),
            $ordenCobro->admin?->name,
            $ordenCobro->comentarios,
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
