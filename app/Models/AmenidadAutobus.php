<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AmenidadAutobus extends ModelHelper
{
    protected $table = 'amenidad_autobus';

    protected $fillable = [
        'autobus_id',
        'amenidad_id',
    ];

    protected function casts(): array
    {
        return [];
    }

    public function autobus(): BelongsTo
    {
        return $this->belongsTo(Autobus::class, 'autobus_id');
    }

    public function amenidad(): BelongsTo
    {
        return $this->belongsTo(Amenidad::class, 'amenidad_id');
    }
}
