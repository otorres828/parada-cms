<?php

namespace App\Http\Middleware;

use App\Models\Admin;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    private const ROUTES_WITHOUT_PERMISSION = [
        'admin.account.profile',
        'admin.account.password',
    ];

    private const ROUTE_PERMISSIONS = [
        'admin.admins.list' => ['admins', 'list'],
        'admin.admins.edit' => ['admins', 'edit'],
        'admin.admins.add' => ['admins', 'add'],
        'admin.amenidades.list' => ['amenidades', 'list'],
        'admin.amenidades.edit' => ['amenidades', 'edit'],
        'admin.amenidades.add' => ['amenidades', 'add'],
        'admin.auditoria.list' => ['auditoria', 'list'],
        'admin.auditoria.detail' => ['auditoria', 'detail'],
        'admin.autobuses.list' => ['autobuses', 'list'],
        'admin.autobuses.detail' => ['autobuses', 'detail'],
        'admin.cupones.list' => ['cupones', 'list'],
        'admin.cupones.detail' => ['cupones', 'detail'],
        'admin.cupones.edit' => ['cupones', 'edit'],
        'admin.cupones.add' => ['cupones', 'add'],
        'admin.dashboard' => ['dashboard', 'list'],
        'admin.empresas.list' => ['empresas', 'list'],
        'admin.empresas.detail' => ['empresas', 'detail'],
        'admin.empresas.edit' => ['empresas', 'edit'],
        'admin.empresas.add' => ['empresas', 'add'],
        'admin.empresas.users.list' => ['empresas.users', 'list'],
        'admin.empresas.users.detail' => ['empresas.users', 'detail'],
        'admin.empresas.users.edit' => ['empresas.users', 'edit'],
        'admin.empresas.users.permissions' => ['empresas.users', 'permissions'],
        'admin.legales.list' => ['legales', 'list'],
        'admin.legales.detail' => ['legales', 'detail'],
        'admin.legales.file' => ['legales', 'file'],
        'admin.ordenes-cobro.list' => ['ordenes-cobro', 'list'],
        'admin.ordenes-cobro.detail' => ['ordenes-cobro', 'detail'],
        'admin.pasajes.list' => ['pasajes', 'list'],
        'admin.pasajes.detail' => ['pasajes', 'detail'],
        'admin.programaciones.list' => ['programaciones', 'list'],
        'admin.programaciones.passengers' => ['programaciones', 'passengers'],
        'admin.reembolsos.list' => ['reembolsos', 'list'],
        'admin.reembolsos.detail' => ['reembolsos', 'detail'],
        'admin.reportes.companies' => ['reportes', 'list-companies'],
        'admin.reportes.sales' => ['reportes', 'list-sales'],
        'admin.reservas.list' => ['reservas', 'list'],
        'admin.reservas.detail' => ['reservas', 'detail'],
        'admin.solicitudes.list' => ['solicitudes', 'list'],
        'admin.solicitudes.detail' => ['solicitudes', 'detail'],
        'admin.tasas-servicio.list' => ['tasas-servicio', 'list'],
        'admin.tasas-servicio.edit' => ['tasas-servicio', 'edit'],
        'admin.tasas-servicio.add' => ['tasas-servicio', 'add'],
        'admin.terminales.list' => ['terminales', 'list'],
        'admin.terminales.edit' => ['terminales', 'edit'],
        'admin.terminales.add' => ['terminales', 'add'],
        'admin.clientes.list' => ['clientes', 'list'],
        'admin.clientes.edit' => ['clientes', 'edit'],
        'admin.clientes.detail' => ['clientes', 'detail'],
        'admin.viajes.list' => ['viajes', 'list'],
        'admin.viajes.detail' => ['viajes', 'detail'],
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
