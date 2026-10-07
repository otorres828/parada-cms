<?php

namespace App\Http\Middleware;

use App\Models\Admin;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckPermissionAdmin
{
    private const ROUTE_PERMISSIONS = [
        /* ----------------------------------------Dashboard---------------------------------------- */
        'admin.dashboard' => ['dashboard', 'list'],

        /* ----------------------------------------Administración---------------------------------- */

        // Administradores
        'admin.admins.list' => ['admins', 'list'],
        'admin.admins.add' => ['admins', 'add'],
        'admin.admins.edit' => ['admins', 'edit'],

        // Tasas de servicio
        'admin.tasas-servicio.list' => ['tasas-servicio', 'list'],
        'admin.tasas-servicio.add' => ['tasas-servicio', 'add'],
        'admin.tasas-servicio.edit' => ['tasas-servicio', 'edit'],

        // Exoneraciones de tasa de servicio
        'admin.exoneraciones-tasa-servicio.list' => ['exoneraciones-tasa-servicio', 'list'],
        'admin.exoneraciones-tasa-servicio.add' => ['exoneraciones-tasa-servicio', 'add'],
        'admin.exoneraciones-tasa-servicio.edit' => ['exoneraciones-tasa-servicio', 'edit'],

        // Auditoría
        'admin.auditoria.list' => ['auditoria', 'list'],
        'admin.auditoria.detail' => ['auditoria', 'detail'],

        /* ----------------------------------------Empresas y clientes------------------------------ */

        // Empresas
        'admin.empresas.list' => ['empresas', 'list'],
        'admin.empresas.add' => ['empresas', 'add'],
        'admin.empresas.edit' => ['empresas', 'edit'],
        'admin.empresas.detail' => ['empresas', 'detail'],
        'admin.empresas.politicas' => ['empresas', 'detail'],

        // Usuarios de empresa
        'admin.empresas.users.list' => ['empresas.users', 'list'],
        'admin.empresas.users.add' => ['empresas.users', 'add'],
        'admin.empresas.users.edit' => ['empresas.users', 'edit'],

        // Clientes
        'admin.clientes.list' => ['clientes', 'list'],
        'admin.clientes.edit' => ['clientes', 'edit'],
        'admin.clientes.detail' => ['clientes', 'detail'],

        /* ----------------------------------------Operación de viajes------------------------------ */

        // Rutas de viajes
        'admin.viajes.list' => ['viajes', 'list'],
        'admin.viajes.detail' => ['viajes', 'detail'],

        // Programaciones
        'admin.programaciones.list' => ['programaciones', 'list'],
        'admin.programaciones.detail' => ['programaciones', 'detail'],

        // Transportes
        'admin.transportes.list' => ['transportes', 'list'],
        'admin.transportes.detail' => ['transportes', 'detail'],

        /* ----------------------------------------Ventas y finanzas------------------------------- */

        // Reservas
        'admin.reservas.list' => ['reservas', 'list'],
        'admin.reservas.detail' => ['reservas', 'detail'],

        // Pasajes
        'admin.pasajes.list' => ['pasajes', 'list'],
        'admin.pasajes.detail' => ['pasajes', 'detail'],

        // Reembolsos
        'admin.reembolsos.list' => ['reembolsos', 'list'],
        'admin.reembolsos.detail' => ['reembolsos', 'detail'],

        // Órdenes de cobro
        'admin.ordenes-cobro.list' => ['ordenes-cobro', 'list'],
        'admin.ordenes-cobro.detail' => ['ordenes-cobro', 'detail'],

        /* ----------------------------------------Promociones-------------------------------------- */

        // Cupones
        'admin.cupones.list' => ['cupones', 'list'],
        'admin.cupones.add' => ['cupones', 'add'],
        'admin.cupones.edit' => ['cupones', 'edit'],
        'admin.cupones.detail' => ['cupones', 'detail'],

        /* ----------------------------------------Reportes----------------------------------------- */
        'admin.reportes.sales' => ['reportes', 'list-sales'],
        'admin.reportes.companies' => ['reportes', 'list-companies'],
        'admin.reportes.exchange-rates' => ['reportes', 'list-exchange-rates'],

        /* ----------------------------------------Legales------------------------------------------ */

        // Documentos
        'admin.legales.documentos.list' => ['legales', 'list'],
        'admin.legales.documentos.detail' => ['legales', 'detail'],
        'admin.legales.documentos.file' => ['legales', 'file'],

        // Contenido de páginas
        'admin.legales.sobre-nosotros.edit' => ['sobre-nosotros', 'edit'],
        'admin.legales.politicas-privacidad.edit' => ['politicas-privacidad', 'edit'],
        'admin.legales.politicas-cookies.edit' => ['politicas-cookies', 'edit'],
        'admin.legales.terminos-condiciones.edit' => ['terminos-condiciones', 'edit'],

        /* ----------------------------------------Catálogos---------------------------------------- */

        // Terminales
        'admin.terminales.list' => ['terminales', 'list'],
        'admin.terminales.add' => ['terminales', 'add'],
        'admin.terminales.edit' => ['terminales', 'edit'],

        // Amenidades
        'admin.amenidades.list' => ['amenidades', 'list'],
        'admin.amenidades.add' => ['amenidades', 'add'],
        'admin.amenidades.edit' => ['amenidades', 'edit'],

        // Preguntas frecuentes
        'admin.preguntas-frecuentes.list' => ['preguntas-frecuentes', 'list'],
        'admin.preguntas-frecuentes.add' => ['preguntas-frecuentes', 'add'],
        'admin.preguntas-frecuentes.edit' => ['preguntas-frecuentes', 'edit'],
        'admin.preguntas-frecuentes.categorias.list' => ['preguntas-frecuentes', 'list'],
        'admin.preguntas-frecuentes.categorias.add' => ['preguntas-frecuentes', 'add'],
        'admin.preguntas-frecuentes.categorias.edit' => ['preguntas-frecuentes', 'edit'],

        /* ----------------------------------------Soporte------------------------------------------ */

        // Solicitudes de empresas
        'admin.solicitudes.list' => ['solicitudes', 'list'],
        'admin.solicitudes.detail' => ['solicitudes', 'detail'],

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
