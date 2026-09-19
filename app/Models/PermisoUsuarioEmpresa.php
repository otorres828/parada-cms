<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PermisoUsuarioEmpresa extends ModelHelper
{
    protected $table = 'permisos_usuarios_empresa';

    protected $fillable = [
        'usuario_empresa_id',
        'permiso_id',
    ];

    protected function casts(): array
    {
        return [];
    }

    public function usuarioEmpresa(): BelongsTo
    {
        return $this->belongsTo(UsuarioEmpresa::class, 'usuario_empresa_id');
    }

    public function permission(): BelongsTo
    {
        return $this->belongsTo(PermissionEmpresa::class, 'permiso_id');
    }
}
