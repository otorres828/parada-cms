<?php

namespace App\Exports\Empresas;

use App\Models\Reserva;
use App\Models\UsuarioEmpresa;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithCustomChunkSize;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;

class ReservasExport extends DefaultValueBinder implements FromQuery, WithCustomChunkSize, WithCustomValueBinder, WithHeadings, WithMapping
{

    public UsuarioEmpresa $usuarioEmpresa;
    public bool $viewTasaServicio;

    public function __construct(
        private Builder $consulta,
        private array $columnasOmitidas = [],
    ) {
        $this->usuarioEmpresa = Auth::guard('empresa')->user();
        $this->viewTasaServicio = $this->usuarioEmpresa->empresa->viewTasaServicio();
    }

    public function query(): Builder
    {
        return $this->consulta->with('tramoPrecio');
    }

    public function chunkSize(): int
    {
        return 1000;
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
            'origen_venta' => $reserva->origen_venta === Reserva::ORIGEN_TAQUILLA ? 'Taquilla' : 'Web',
            'receptor_pago' => $reserva->receptor_pago === 'empresa' ? 'Empresa' : ($reserva->receptor_pago === 'plataforma' ? 'Plataforma' : 'No registrado'),
            'tipo_transporte' => $reserva->programacion?->transporte?->getTipoTransporte(),
            'reprogramacion' => $reserva->reservaOriginal?->codigo_referencia,
            'fecha_reserva' => $reserva->fecha_compra?->format('d/m/Y H:i'),
            'cliente' => $reserva->nombre_comprador,
            'correo_cliente' => $reserva->comprador_json['email'] ?? $reserva->usuario?->email,
            'empresa' => $reserva->programacion?->viaje?->empresa?->nombre,
            'origen' => $reserva->origenTerminal?->nombre,
            'destino' => $reserva->destinoTerminal?->nombre,
            'cantidad_pasajes' => $reserva->pasajes->count(),
            'precio' => (float) $reserva->monto_pasajes,
            'precio_bs' => (float) $reserva->calcularMontoBs($reserva->monto_pasajes),
            'descuento' => (float) $reserva->descuento_aplicado,
            'descuento_bs' => (float) $reserva->calcularMontoBs($reserva->descuento_aplicado)
        ];

        if($this->viewTasaServicio){
            $valores = array_merge($valores, 
            [
                'subtotal' => (float) $totalSinTasa,
                'subtotal_bs' => (float) $reserva->calcularMontoBs($totalSinTasa),
                'tasa_servicio' => (float) $reserva->tasa_servicio,
                'tasa_servicio_bs' => (float) $reserva->calcularMontoBs($reserva->tasa_servicio),
                'total' => (float) $reserva->monto_total,
                'total_bs' => (float) $reserva->calcularMontoBs($reserva->monto_total),
                'cupon' => $reserva->cupon?->codigo,
                'estado_pago' => $reserva->getStatusPago(),
                'fecha_salida' => $reserva->tramoPrecio?->getSalida()?->format('d/m/Y'),
                'hora_salida' => $reserva->tramoPrecio?->getSalida()?->format('H:i:s'),
            ]);
        }else{
            $valores = array_merge($valores, 
            [
                'total' => (float) $totalSinTasa,
                'total_bs' => (float) $reserva->calcularMontoBs($totalSinTasa),
                'cupon' => $reserva->cupon?->codigo,
                'estado_pago' => $reserva->getStatusPago(),
                'fecha_salida' => $reserva->tramoPrecio?->getSalida()?->format('d/m/Y'),
                'hora_salida' => $reserva->tramoPrecio?->getSalida()?->format('H:i:s'),
            ]);    
        }
        

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
        $headings = [
            'id' => 'ID reserva',
            'referencia' => 'Referencia',
            'origen_venta' => 'Origen de venta',
            'receptor_pago' => 'Receptor del pago',
            'tipo_transporte' => 'Tipo de transporte',
            'reprogramacion' => 'Reprogramación',
            'fecha_reserva' => 'Fecha de reserva',
            'cliente' => 'Cliente',
            'correo_cliente' => 'Correo del cliente',
            'empresa' => 'Empresa',
            'origen' => 'Origen',
            'destino' => 'Destino final',
            'cantidad_pasajes' => 'Cantidad de pasajes',
            'precio' => 'Precio USD',
            'precio_bs' => 'Precio Bs',
            'descuento' => 'Descuento USD',
            'descuento_bs' => 'Descuento Bs'
        ];

        if($this->viewTasaServicio){
            $headings = array_merge($headings, 
            [
                'subtotal' => 'SubTotal USD',
                'subtotal_bs' => 'SubTotal Bs',
                'tasa_servicio' => 'Tasa de servicio USD',
                'tasa_servicio_bs' => 'Tasa de servicio Bs',
                'total' => 'Total USD',
                'total_bs' => 'Total Bs',
                'cupon' => 'Cupón',
                'estado_pago' => 'Estado de pago',
                'fecha_salida' => 'Fecha de salida',
                'hora_salida' => 'Hora de salida',
            ]);
        }else{
            $headings = array_merge($headings, 
            [
                'total' => 'Total USD',
                'total_bs' => 'Total Bs',
                'cupon' => 'Cupón',
                'estado_pago' => 'Estado de pago',
                'fecha_salida' => 'Fecha de salida',
                'hora_salida' => 'Hora de salida',
            ]);
        }

        return $headings;
    
    }

    private function filtrarColumnas(array $columnas): array
    {
        return array_diff_key($columnas, array_flip($this->columnasOmitidas));
    }
}
