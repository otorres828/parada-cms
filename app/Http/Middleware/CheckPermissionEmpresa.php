<?php

namespace App\Http\Middleware;

use App\Models\Admin;
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
        'admin.account.profile',
        'admin.account.password',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $route = $request->route()?->getName();

        if (! $route || ! str_starts_with($route, 'admin.') || in_array($route, ['admin.login', 'admin.auth.logout'], true)) {
            return $next($request);
        }

        if (! Auth::guard('admin')->check()) {
            return redirect()->route('admin.login');
        }

        $admin = Admin::find(Auth::guard('admin')->id());
        abort_unless($admin && $admin->status === Admin::ACTIVO, 403);

        if (in_array($route, self::ROUTES_WITHOUT_PERMISSION, true)) {
            return $next($request);
        }

        abort_unless(isset(self::ROUTE_PERMISSIONS[$route]), 403);
        [$section, $action] = self::ROUTE_PERMISSIONS[$route];

        if (! $admin->hasPermission($section, $action)) {
            // Evitar ciclos si el administrador tampoco tiene permiso para Dashboard.
            $destination = $admin->hasPermission('dashboard', 'list') ? 'admin.dashboard' : 'admin.account.profile';
            abort_if($route === $destination, 403);

            return redirect()->route($destination);
        }

        return $next($request);
    }
}
