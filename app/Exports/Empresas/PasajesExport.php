<?php

namespace App\Exports\Empresas;

use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithCustomChunkSize;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;

class PasajesExport extends DefaultValueBinder implements FromQuery, WithCustomChunkSize, WithCustomValueBinder, WithHeadings, WithMapping
{
    public function __construct(
        private Builder $consulta,
        private bool $mostrarTasaServicio,
    ) {}

    public function query(): Builder
    {
        return $this->consulta->with('reserva.tramoPrecio.programacion.viaje');
    }

    public function chunkSize(): int
    {
        return 1000;
    }

    public function headings(): array
    {
        $columnas = [
            'ID pasaje', 
            'Reserva', 
            'Fecha de reserva', 
            'Viajero', 
            'Documento', 
            'Fecha de nacimiento', 
            'Asiento',
            'Precio base USD',
            'Precio base Bs',
            'Descuento USD',
            'Descuento Bs',
            'Subtotal USD', //columna 11
            'Subtotal Bs',  //columna 12
            'Tasa de servicio USD',
            'Tasa de servicio Bs',
            'Total USD',
            'Total Bs',
            'Abordado', 
            'Estado de pago', 
            'Origen', 
            'Destino final',
            'Fecha de salida', 
            'Hora de salida',
        ];

        // Si la empresa no tiene contrato "Ellos reciben", no se muestran  las
        // columnas de tasa de servicio y se ajustan los name de las columnas 11 y 12.
        if (! $this->mostrarTasaServicio) {
            $columnas[11] = 'Total USD';
            $columnas[12] = 'Total Bs';
            array_splice($columnas, 13, 4);
        }

        return $columnas;
    }

    public function map($pasaje): array
    {
        $reserva = $pasaje->reserva;
        $salida = $reserva?->tramoPrecio?->getSalida();

        $valores = [
            $pasaje->id, 
            $reserva?->codigo_referencia, 
            $reserva?->fecha_compra?->format('d/m/Y H:i'),
            $pasaje->viajero_nombre_completo,
            $pasaje->viajero_documento,
            $pasaje->viajero_fecha_nacimiento?->format('d/m/Y'),
            $pasaje->numero_asiento,
            (float) $pasaje->precio_base,
            (float) $pasaje->calcularMontoBs($pasaje->precio_base),
            (float) $pasaje->descuento,
            (float) $pasaje->calcularMontoBs($pasaje->descuento),
            (float) $pasaje->subtotal,
            (float) $pasaje->calcularMontoBs($pasaje->subtotal),
            (float) $pasaje->tasa_servicio,
            (float) $pasaje->calcularMontoBs($pasaje->tasa_servicio),
            (float) $pasaje->total,
            (float) $pasaje->calcularMontoBs($pasaje->total),
            $pasaje->abordado ? 'Sí' : 'No',
            $reserva?->getStatusPago(), 
            $reserva?->origenTerminal?->nombre,
            $reserva?->destinoTerminal?->nombre,
            $salida?->format('d/m/Y'), 
            $salida?->format('H:i:s')
        ];

        if (! $this->mostrarTasaServicio) {
            array_splice($valores, 13, 4);
        }

        return $valores;
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
