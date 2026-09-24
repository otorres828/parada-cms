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

class ReservasExport extends DefaultValueBinder implements FromQuery, WithCustomValueBinder, WithHeadings, WithMapping
{
    public function __construct(private Builder $consulta) {}

    public function query(): Builder
    {
        return $this->consulta;
    }

    public function headings(): array
    {
        return [
            'ID reserva',
            'Referencia',
            'Fecha de reserva',
            'Cliente',
            'Correo del cliente',
            'Empresa',
            'Origen',
            'Destino final',
            'Cantidad de pasajes',
            'Subtotal',
            'Descuento',
            'Total',
            'Tasa de servicio',
            'Total + tasa de servicio',
            'Cupón',
            'Estado de pago',
            'Fecha de salida',
            'Hora de salida',
        ];
    }

    public function map($reserva): array
    {
        return [
            $reserva->id,
            $reserva->codigo_referencia,
            $reserva->fecha_compra?->format('d/m/Y H:i'),
            $reserva->usuario?->name,
            $reserva->usuario?->email,
            $reserva->programacion?->viaje?->empresa?->nombre,
            $reserva->origenTerminal?->nombre,
            $reserva->destinoTerminal?->nombre,
            $reserva->pasajes_count,
            (float) $reserva->monto_pasajes,
            (float) $reserva->descuento_aplicado,
            (float) $reserva->monto_pasajes - (float) $reserva->descuento_aplicado,
            (float) $reserva->tasa_servicio,
            (float) $reserva->monto_total,
            $reserva->cupon?->codigo,
            $reserva->getStatusPago(),
            $reserva->programacion?->fecha_salida?->format('d/m/Y'),
            $reserva->programacion?->hora_salida,
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
