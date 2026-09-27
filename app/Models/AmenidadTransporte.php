<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AmenidadTransporte extends ModelHelper
{
    protected $table = 'amenidad_transporte';

    protected $fillable = [
        'transporte_id',
        'amenidad_id',
    ];

    protected function casts(): array
    {
        return [];
    }

    public function transporte(): BelongsTo
    {
        return $this->belongsTo(Transporte::class, 'transporte_id');
    }

    public function amenidad(): BelongsTo
    {
        return $this->belongsTo(Amenidad::class, 'amenidad_id');
    }
}
