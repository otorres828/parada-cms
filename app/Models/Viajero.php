<?php

namespace App\Models;

use App\Support\PersonalData;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Viajero extends ModelHelper
{
    public const DOCUMENTO_CEDULA = 1;

    public const DOCUMENTO_DNI_EXTERIOR = 2;

    public const DOCUMENTO_PASAPORTE = 3;

    public const DOCUMENTO_OTRO_DOCUMENTO = 4;

    protected $table = 'viajeros';

    protected $hidden = ['documento_identidad_hash'];

    protected $fillable = [
        'usuario_id',
        'nombre',
        'apellido',
        'tipo_documento',
        'documento_identidad',
        'documento_identidad_hash',
        'fecha_nacimiento',
        'tipo_pasajero',
        'estatus',
    ];

    protected function casts(): array
    {
        return [
            'tipo_documento' => 'integer',
            'documento_identidad' => 'encrypted',
            'fecha_nacimiento' => 'date',
            'estatus' => 'integer',
        ];
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
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

    protected static function booted(): void
    {
        static::saving(function (Viajero $viajero) {
            if ($viajero->isDirty('documento_identidad')) {
                $viajero->documento_identidad_hash = PersonalData::hashDocumento($viajero->documento_identidad);
            }
        });
    }
}
