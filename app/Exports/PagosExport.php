<?php
namespace App\Exports;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
class PagosExport extends DefaultValueBinder implements FromQuery, WithHeadings, WithMapping, WithColumnFormatting, ShouldAutoSize, WithCustomValueBinder, WithStyles
{
    public function __construct(private Builder $pagos) {}
    public function query() { return clone $this->pagos; }
    public function headings(): array { return ['ID','Referencia de pago','Reserva','Empresa','Fecha de pago','Método','Recibido USD','Tasa de servicio USD','Neto empresa USD','Comisión histórica USD','Estado de la reserva','Registrado por']; }
    public function map($pago): array
    {
        return [$pago->id,$pago->referencia,$pago->reserva?->codigo_referencia,$pago->empresa?->nombre,$pago->fecha_pago?->format('Y-m-d H:i'),$pago->metodo,(float)$pago->monto,(float)($pago->reserva?->tasa_servicio??0),(float)$pago->neto_empresa,(float)$pago->comision,$pago->reserva?->estado_pago,$pago->admin?->name];
    }
    public function bindValue(Cell $cell, $value): bool
    {
        if (is_string($value)) { $cell->setValueExplicit($value,DataType::TYPE_STRING); return true; }
        return parent::bindValue($cell,$value);
    }
    public function columnFormats(): array { return array_fill_keys(['G','H','I','J'],'#,##0.00'); }
    public function styles(Worksheet $sheet)
    {
        $sheet->freezePane('A2');
        $sheet->setAutoFilter($sheet->calculateWorksheetDimension());
        return [1=>['font'=>['bold'=>true]]];
    }
}
