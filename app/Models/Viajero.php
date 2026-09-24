<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Viajero extends ModelHelper
{
    public const DOCUMENTO_CEDULA = 1;

    public const DOCUMENTO_DNI_EXTERIOR = 2;

    public const DOCUMENTO_PASAPORTE = 3;

    public const DOCUMENTO_OTRO_DOCUMENTO = 4;

    protected $table = 'viajeros';

    protected $fillable = [
        'usuario_id',
        'nombre',
        'apellido',
        'tipo_documento',
        'documento_identidad',
        'fecha_nacimiento',
        'tipo_pasajero',
        'estatus',
    ];

    protected function casts(): array
    {
        return [
            'tipo_documento' => 'integer',
            'fecha_nacimiento' => 'date',
            'estatus' => 'integer',
        ];
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function pasajes(): HasMany
    {
        return $this->hasMany(Pasaje::class, 'viajero_id');
    }

    public function getTipoDocumento(): string
    {
        return match ($this->tipo_documento) {
            self::DOCUMENTO_CEDULA => 'Cédula',
            self::DOCUMENTO_DNI_EXTERIOR => 'DNI extranjero',
            self::DOCUMENTO_PASAPORTE => 'Pasaporte',
            self::DOCUMENTO_OTRO_DOCUMENTO => 'Otro',
            default => 'Sin documento',
        };
    }
}
