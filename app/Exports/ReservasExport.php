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
    public function __construct(
        private Builder $consulta,
        private array $columnasOmitidas = [],
    ) {}

    public function query(): Builder
    {
        return $this->consulta;
    }

    public function headings(): array
    {
        return array_values($this->filtrarColumnas($this->encabezados()));
    }

    public function map($reserva): array
    {
        $totalSinTasa = bcsub($reserva->monto_pasajes, $reserva->descuento_aplicado, 2);
        $valores = [
            'id' => $reserva->id,
            'referencia' => $reserva->codigo_referencia,
            'reprogramacion' => $reserva->reservaOriginal?->codigo_referencia,
            'fecha_reserva' => $reserva->fecha_compra?->format('d/m/Y H:i'),
            'cliente' => $reserva->usuario?->name,
            'correo_cliente' => $reserva->usuario?->email,
            'empresa' => $reserva->programacion?->viaje?->empresa?->nombre,
            'origen' => $reserva->origenTerminal?->nombre,
            'destino' => $reserva->destinoTerminal?->nombre,
            'cantidad_pasajes' => $reserva->pasajes_count,
            'subtotal' => (float) $reserva->monto_pasajes,
            'subtotal_bs' => (float) $reserva->calcularMontoBs($reserva->monto_pasajes),
            'descuento' => (float) $reserva->descuento_aplicado,
            'descuento_bs' => (float) $reserva->calcularMontoBs($reserva->descuento_aplicado),
            'total' => (float) $totalSinTasa,
            'total_bs' => (float) $reserva->calcularMontoBs($totalSinTasa),
            'tasa_servicio' => (float) $reserva->tasa_servicio,
            'tasa_servicio_bs' => (float) $reserva->calcularMontoBs($reserva->tasa_servicio),
            'total_tasa_servicio' => (float) $reserva->monto_total,
            'total_tasa_servicio_bs' => (float) $reserva->calcularMontoBs($reserva->monto_total),
            'cupon' => $reserva->cupon?->codigo,
            'estado_pago' => $reserva->getStatusPago(),
            'fecha_salida' => $reserva->programacion?->fecha_salida?->format('d/m/Y'),
            'hora_salida' => $reserva->programacion?->hora_salida,
        ];

        return array_values($this->filtrarColumnas($valores));
    }

    public function bindValue(Cell $cell, mixed $value): bool
    {
        if (is_string($value)) {
            $cell->setValueExplicit($value, DataType::TYPE_STRING);

            return true;
        }

        return parent::bindValue($cell, $value);
    }

    private function encabezados(): array
    {
        return [
            'id' => 'ID reserva',
            'referencia' => 'Referencia',
            'reprogramacion' => 'Reprogramación',
            'fecha_reserva' => 'Fecha de reserva',
            'cliente' => 'Cliente',
            'correo_cliente' => 'Correo del cliente',
            'empresa' => 'Empresa',
            'origen' => 'Origen',
            'destino' => 'Destino final',
            'cantidad_pasajes' => 'Cantidad de pasajes',
            'subtotal' => 'Subtotal USD',
            'subtotal_bs' => 'Subtotal Bs',
            'descuento' => 'Descuento USD',
            'descuento_bs' => 'Descuento Bs',
            'total' => 'Total USD',
            'total_bs' => 'Total Bs',
            'tasa_servicio' => 'Tasa de servicio USD',
            'tasa_servicio_bs' => 'Tasa de servicio Bs',
            'total_tasa_servicio' => 'Total + tasa de servicio USD',
            'total_tasa_servicio_bs' => 'Total + tasa de servicio Bs',
            'cupon' => 'Cupón',
            'estado_pago' => 'Estado de pago',
            'fecha_salida' => 'Fecha de salida',
            'hora_salida' => 'Hora de salida',
        ];
    }

    private function filtrarColumnas(array $columnas): array
    {
        return array_diff_key($columnas, array_flip($this->columnasOmitidas));
    }
}
