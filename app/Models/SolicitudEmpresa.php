<?php

namespace App\Models;

class SolicitudEmpresa extends ModelHelper
{
    public const ESTADO_NUEVA = 1;

    public const ESTADO_CONTACTADA = 2;

    public const ESTADO_CERRADA = 3;

    protected $table = 'solicitudes_empresas';

    protected $fillable = [
        'nombre',
        'empresa',
        'cargo',
        'telefono',
        'email',
        'ciudad',
        'mensaje',
        'estatus',
    ];

    protected function casts(): array
    {
        return [
            'estatus' => 'integer',
        ];
    }
}
