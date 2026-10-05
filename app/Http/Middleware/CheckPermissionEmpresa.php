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

        /* ----------------------------------------Administración---------------------------------------- */
        'empresas.usuarios.list' => ['usuarios', 'list'],
        'empresas.politicas-embarque.edit' => ['politicas-embarque', 'edit'],

        'empresas.datos-bancarios.list' => ['datos-bancarios', 'list'],
        'empresas.datos-bancarios.add' => ['datos-bancarios', 'add'],
        'empresas.datos-bancarios.edit' => ['datos-bancarios', 'edit'],

        /* ----------------------------------------Operación de viajes---------------------------------------- */
        
        // Rutas de viajes
        'empresas.viajes.list' => ['viajes', 'list'],
        'empresas.viajes.detail' => ['viajes', 'detail'],
        'empresas.viajes.add' => ['viajes', 'add'],
        'empresas.viajes.edit' => ['viajes', 'edit'],

        // Programaciones
        'empresas.programaciones.list' => ['programaciones', 'list'],
        'empresas.programaciones.detail' => ['programaciones', 'detail'],
        'empresas.programaciones.add' => ['programaciones', 'add'],
        'empresas.programaciones.edit' => ['programaciones', 'edit'],

        // Transportes
        'empresas.transportes.list' => ['transportes', 'list'],
        'empresas.transportes.detail' => ['transportes', 'detail'],
        'empresas.transportes.add' => ['transportes', 'add'],
        'empresas.transportes.edit' => ['transportes', 'edit'],

        /* ----------------------------------------Ventas y finanzas------------------------------- */

        // Reservas
        'empresas.reservas.list' => ['reservas', 'list'],
        'empresas.reservas.detail' => ['reservas', 'detail'],
        'empresas.reservas.add' => ['reservas', 'add'],

        // Pasajes
        'empresas.pasajes.list' => ['pasajes', 'list'],
        'empresas.pasajes.detail' => ['pasajes', 'detail'],

        'empresas.validacion-pagos.list' => ['validacion-pagos', 'list'],
        'empresas.reembolsos.list' => ['reembolsos', 'list'],
        'empresas.reprogramaciones.list' => ['reprogramaciones', 'list'],

        /* ----------------------------------------Cobranza---------------------------------------- */
        'empresas.ordenes-cobro.list' => ['ordenes-cobro', 'list'],

        /* ----------------------------------------Promociones---------------------------------------- */
        'empresas.cupones.list' => ['cupones', 'list'],
        'empresas.cupones.add' => ['cupones', 'add'],
        'empresas.cupones.edit' => ['cupones', 'edit'],

        /* ----------------------------------------Reportes---------------------------------------- */
        'empresas.reportes.ventas' => ['reporte-ventas', 'list'],
        'empresas.reportes.rutas' => ['reporte-rutas', 'list'],
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
