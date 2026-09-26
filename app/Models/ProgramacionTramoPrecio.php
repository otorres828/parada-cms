<?php

namespace App\Models;

use App\Support\ConversorMoneda;
use App\Traits\TraitGeneral;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProgramacionTramoPrecio extends ModelHelper
{
    use TraitGeneral;

    protected $table = 'programacion_tramo_precios';

    protected $fillable = [
        'programacion_id',
        'origen_terminal_id',
        'destino_terminal_id',
        'precio',
        'asientos_maximos_permitidos',
    ];

    protected function casts(): array
    {
        return [
            'precio' => 'decimal:2',
            'asientos_maximos_permitidos' => 'integer',
        ];
    }

    public function programacion(): BelongsTo
    {
        return $this->belongsTo(Programacion::class, 'programacion_id');
    }

    public function origenTerminal(): BelongsTo
    {
        return $this->belongsTo(Terminal::class, 'origen_terminal_id');
    }

    public function destinoTerminal(): BelongsTo
    {
        return $this->belongsTo(Terminal::class, 'destino_terminal_id');
    }

    public function calcularMontoBs(?TipoCambio $tipoCambio = null): ?string
    {
        return ConversorMoneda::aBolivares($this->precio, $tipoCambio ?? TipoCambio::vigente());
    }
}
