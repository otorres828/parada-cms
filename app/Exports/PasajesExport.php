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

class PasajesExport extends DefaultValueBinder implements FromQuery, WithCustomValueBinder, WithHeadings, WithMapping
{
    public function __construct(private Builder $consulta) {}

    public function query(): Builder
    {
        return $this->consulta;
    }

    public function headings(): array
    {
        return [
            'ID pasaje', 
            'Reserva', 
            'Fecha de reserva', 
            'Viajero', 
            'Documento', 
            'Fecha de nacimiento', 
            'Asiento',
            'Precio base', 
            'Descuento', 
            'Subtotal', 
            'Tasa de servicio', 
            'Total',
            'Abordado', 
            'Estado de pago', 
            'Origen', 
            'Destino final',
            'Fecha de salida', 
            'Hora de salida'];
    }

    public function map($pasaje): array
    {
        $reserva = $pasaje->reserva;
        $programacion = $reserva?->programacion;

        return [
            $pasaje->id, 
            $reserva?->codigo_referencia, 
            $reserva?->fecha_compra?->format('d/m/Y H:i'),
            trim(($pasaje->viajero?->nombre ?? '').' '.($pasaje->viajero?->apellido ?? '')),
            $pasaje->viajero?->documento_identidad, 
            $pasaje->viajero?->fecha_nacimiento?->format('d/m/Y'),
            $pasaje->numero_asiento,
            (float) $pasaje->precio_base,
             (float) $pasaje->descuento, 
             (float) $pasaje->subtotal,
            (float) $pasaje->tasa_servicio, 
            (float) $pasaje->total,
            $pasaje->abordado ? 'Sí' : 'No',
            $reserva?->getStatusPago(), 
            $reserva?->origenTerminal?->nombre,
            $reserva?->destinoTerminal?->nombre,
            $programacion?->fecha_salida?->format('d/m/Y'), 
            $programacion?->hora_salida
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
