<?php

namespace App\Models;

use App\Traits\TraitGeneral;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ViajeTramo extends ModelHelper
{
    use TraitGeneral;

    protected $table = 'viaje_tramos';

    protected $fillable = [
        'viaje_id',
        'origen_terminal_id',
        'destino_terminal_id',
        'orden',
        'duracion_estimada',
    ];

    protected function casts(): array
    {
        return [
            'orden' => 'integer',
        ];
    }

    public function viaje(): BelongsTo
    {
        return $this->belongsTo(Viaje::class, 'viaje_id');
    }

    public function origenTerminal(): BelongsTo
    {
        return $this->belongsTo(Terminal::class, 'origen_terminal_id');
    }

    public function destinoTerminal(): BelongsTo
    {
        return $this->belongsTo(Terminal::class, 'destino_terminal_id');
    }
}
