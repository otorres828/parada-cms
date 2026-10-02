<?php

namespace App\Http\Middleware;

use App\Models\UsuarioEmpresa;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckPermissionEmpresa
{
    private const ROUTE_PERMISSIONS = [

        /* ----------------------------------------Dashboard---------------------------------------- */
        'empresas.dashboard' => ['dashboard', 'list'],

        /* ----------------------------------------Administración---------------------------------- */


    ];

    /* ----------------------------------------Mi cuenta---------------------------------------- */
    private const ROUTES_WITHOUT_PERMISSION = [
        'empresas.account.profile',
        'empresas.account.password',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $route = $request->route()?->getName();

        if (! $route || ! str_starts_with($route, 'empresas.') || in_array($route, ['empresas.login', 'empresas.auth.logout'], true)) {
            return $next($request);
        }

        if (! Auth::guard('empresa')->check()) {
            return redirect()->route('empresas.login');
        }

        $usuario = UsuarioEmpresa::find(Auth::guard('empresa')->id());
        abort_unless($usuario && $usuario->estatus === UsuarioEmpresa::ESTADO_ACTIVE, 403);

        if (in_array($route, self::ROUTES_WITHOUT_PERMISSION, true)) {
            return $next($request);
        }

        abort_unless(isset(self::ROUTE_PERMISSIONS[$route]), 403);
        [$section, $action] = self::ROUTE_PERMISSIONS[$route];

        if (! $usuario->hasPermission($section, $action)) {
            // Evitar ciclos si el usuario tampoco tiene permiso para Dashboard.
            $destination = $usuario->hasPermission('dashboard', 'list') ? 'empresas.dashboard' : 'empresas.account.profile';
            abort_if($route === $destination, 403);

            return redirect()->route($destination);
        }

        return $next($request);
    }
}
