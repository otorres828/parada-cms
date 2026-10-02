<?php

namespace App\Services\Empresa;

use App\Models\UsuarioEmpresa;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class Access
{
    public static function allows(string $module, string $action): bool
    {
        $usuario = UsuarioEmpresa::find(auth('empresa')->id());

        return $usuario?->hasPermission($module, $action) ?? false;
    }

    public static function authorize(string $module, string $action): void
    {
        abort_unless(self::allows($module, $action), 403, 'No tienes permiso para realizar esta acción.');
    }

    public static function permissions(UsuarioEmpresa $usuario): Collection
    {
        return DB::table('permissions_empresa')
            ->join('sections_empresa', 'sections_empresa.id', '=', 'permissions_empresa.section_id')
            ->join('groups_empresa', 'groups_empresa.id', '=', 'sections_empresa.group_id')
            ->join('permisos_usuarios_empresa', 'permisos_usuarios_empresa.permiso_id', '=', 'permissions_empresa.id')
            ->where('permisos_usuarios_empresa.usuario_empresa_id', $usuario->id)
            ->where('permissions_empresa.status', 1)
            ->where('sections_empresa.status', 1)
            ->where('groups_empresa.status', 1)
            ->select('sections_empresa.url as section_url', 'permissions_empresa.url as permission_url')
            ->get();
    }

}
