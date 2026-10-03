<?php

namespace App\Exports;

use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithCustomChunkSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class SalesReportExport implements FromQuery, WithCustomChunkSize, WithHeadings, WithMapping
{

    public bool $viewTasaServicio;

    public function __construct(private Builder $consulta, bool $viewTasaServicio = true) {
        $this->viewTasaServicio = $viewTasaServicio;
    }

    public function query(): Builder
    {
        return $this->consulta;
    }

    public function chunkSize(): int
    {
        return 1000;
    }

    public function headings(): array
    {
        $headings= [
            'Fecha',
            'Reservas pagadas',
            'Ventas USD',
            'Ventas Bs'
        ];
        if($this->viewTasaServicio){
            $headings[] = 'Tasas de servicio USD';
            $headings[] = 'Tasas de servicio Bs';
        }
        return $headings;
           
    }

    public function map($row): array
    {

        if($this->viewTasaServicio){

            return [
                $row->fecha,
                (int) $row->cantidad,
                (float) $row->total,
                (float) $row->total_bs,
                (float) $row->tasas,
                (float) $row->tasas_bs,
            ];

        }

        return [
            $row->fecha,
            (int) $row->cantidad,
            (float) $row->total - (float) $row->tasas,
            (float) $row->total_bs - (float) $row->tasas_bs,
        ];

    }
}
