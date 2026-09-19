<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Viajero extends ModelHelper
{
    protected $table = 'viajeros';

    protected $fillable = [
        'usuario_id',
        'nombre',
        'apellido',
        'documento_identidad',
        'fecha_nacimiento',
        'tipo_pasajero',
    ];

    protected function casts(): array
    {
        return ['fecha_nacimiento' => 'date'];
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function pasajes(): HasMany
    {
        return $this->hasMany(Pasaje::class, 'viajero_id');
    }
}
