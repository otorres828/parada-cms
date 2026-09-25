<?php

use App\Http\Controllers\Admin\DocumentoLegalController;
/*
|--------------------------------------------------------------------------
| Livewire Classes
|--------------------------------------------------------------------------
*/

/* ------------------------------Autenticacion----------------------------------- */
use App\Livewire\Admin\Account\Password;
/* ------------------------------Dashboard--------------------------------------- */
use App\Livewire\Admin\Account\Profile;
/* ------------------------------Perfil------------------------------------------ */
use App\Livewire\Admin\Admins\ListAdmin;
use App\Livewire\Admin\Admins\SaveAdmin;
/* ------------------------------Administradores---------------------------------- */
use App\Livewire\Admin\Amenidades\ListAmenidad;
use App\Livewire\Admin\Amenidades\SaveAmenidad;
/* ------------------------------Tasa de Servicio--------------------------------- */
use App\Livewire\Admin\Auditoria\DetailAudit;
use App\Livewire\Admin\Auditoria\ListAudit;
/* ------------------------------Auditoria---------------------------------------- */
use App\Livewire\Admin\Auth\Login;
use App\Livewire\Admin\Autobuses\DetailAutobus;
/* ------------------------------Empresas----------------------------------------- */
use App\Livewire\Admin\Autobuses\ListAutobus;
use App\Livewire\Admin\Clientes\DetailCliente;
use App\Livewire\Admin\Clientes\ListCliente;
/* ------------------------------Usuarios Empresas-------------------------------- */
use App\Livewire\Admin\Clientes\SaveCliente;
use App\Livewire\Admin\Cupones\DetailCampana;
use App\Livewire\Admin\Cupones\ListCampana;
use App\Livewire\Admin\Cupones\SaveCampana;
/* ------------------------------Clientes------------------------------------ ----- */
use App\Livewire\Admin\Dashboard;
use App\Livewire\Admin\Empresas\DetailEmpresa;
use App\Livewire\Admin\Empresas\ListEmpresa;
/* ------------------------------Legales------------------------------------------- */
use App\Livewire\Admin\Empresas\SaveEmpresa;
use App\Livewire\Admin\EmpresaUsers\DetailEmpresaUser;
use App\Livewire\Admin\EmpresaUsers\ListEmpresaUser;
/* ------------------------------Rutas de viaje------------------------------------ */
use App\Livewire\Admin\EmpresaUsers\PermissionEmpresaUser;
use App\Livewire\Admin\EmpresaUsers\SaveEmpresaUser;
/* ------------------------------Programaciones------------------------------------ */
use App\Livewire\Admin\Legales\EmpresaLegal;
use App\Livewire\Admin\Legales\ListLegal;
use App\Livewire\Admin\OrdenesCobro\DetailOrdenCobro;
use App\Livewire\Admin\OrdenesCobro\ListOrdenCobro;
/* ------------------------------Autobuses----------------------------------------- */
use App\Livewire\Admin\Pasajes\DetailPasaje;
use App\Livewire\Admin\Pasajes\ListPasaje;
/* ------------------------------Reservas------------------------------------------ */
use App\Livewire\Admin\Programaciones\ListProgramacion;
use App\Livewire\Admin\Programaciones\PassengerProgramacion;
/* ------------------------------Pasajes------------------------------------------- */
use App\Livewire\Admin\Reembolsos\DetailReembolso;
use App\Livewire\Admin\Reembolsos\ListReembolso;
/* ------------------------------Reembolsos---------------------------------------- */
use App\Livewire\Admin\Reportes\CompaniesReport;
use App\Livewire\Admin\Reportes\SalesReport;
/* ------------------------------Cupones------------------------------------------- */
use App\Livewire\Admin\Reservas\DetailReserva;
use App\Livewire\Admin\Reservas\ListReserva;
use App\Livewire\Admin\Solicitudes\DetailSolicitud;
use App\Livewire\Admin\Solicitudes\ListSolicitud;
use App\Livewire\Admin\TasasServicio\ListTasaServicio;
/* ------------------------------Terminales---------------------------------------- */
use App\Livewire\Admin\TasasServicio\SaveTasaServicio;
use App\Livewire\Admin\Terminales\ListTerminal;
/* ------------------------------Amenidades---------------------------------------- */
use App\Livewire\Admin\Terminales\SaveTerminal;
use App\Livewire\Admin\Viajes\DetailViaje;
/* ------------------------------Reporte de Ventas generales----------------------- */
use App\Livewire\Admin\Viajes\ListViaje;
/* ------------------------------Reporte de Ventas de Empresas--------------------- */
use Illuminate\Support\Facades\Route;

/* ------------------------------Configuraciones----------------------------------- */

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
| bootstrap/app.php aplica el prefijo /admin y el nombre admin.
*/

Route::livewire('/', Login::class)->name('login');

Route::post('logout', [Login::class, 'logout'])
    ->middleware('auth:admin')
    ->name('auth.logout');

/*
|--------------------------------------------------------------------------
| Protected Routes
|--------------------------------------------------------------------------
*/

Route::group(['middleware' => ['auth:admin', 'check.permisos']], function () {

    Route::livewire('dashboard', Dashboard::class)->name('dashboard');

    /* ----------------------------------------Administradores-------------------------------------- */

    Route::prefix('administradores')->name('admins.')->group(function () {

        Route::livewire('/', ListAdmin::class)->name('list');
        Route::livewire('nuevo', SaveAdmin::class)->name('add');
        Route::livewire('editar/{admin_id}', SaveAdmin::class)->whereNumber('admin_id')->name('edit');

    });

    /* ----------------------------------------Tasas de servicio-------------------------------------- */

    Route::prefix('tasas-servicio')->name('tasas-servicio.')->group(function () {

        Route::livewire('/', ListTasaServicio::class)->name('list');
        Route::livewire('nuevo', SaveTasaServicio::class)->name('add');
        Route::livewire('editar/{tasa_servicio_id}', SaveTasaServicio::class)->whereNumber('tasa_servicio_id')->name('edit');

    });

    /* ----------------------------------------Auditoria-------------------------------------- */

    Route::prefix('auditoria')->name('auditoria.')->group(function () {

        Route::livewire('/', ListAudit::class)->name('list');
        Route::livewire('detalle/{audit_id}', DetailAudit::class)->whereNumber('audit_id')->name('detail');

    });

    /* ----------------------------------------Empresas-------------------------------------- */

    Route::prefix('empresas')->name('empresas.')->group(function () {

        Route::livewire('/', ListEmpresa::class)->name('list');
        Route::livewire('nuevo', SaveEmpresa::class)->name('add');
        Route::livewire('editar/{empresa_id}', SaveEmpresa::class)->whereNumber('empresa_id')->name('edit');
        Route::livewire('detalle/{empresa_id}', DetailEmpresa::class)->whereNumber('empresa_id')->name('detail');

    });

    /* ----------------------------------------Usuarios de empresa-------------------------------------- */

    Route::prefix('empresas/{empresa_id}/usuarios')->name('empresas.users.')->group(function () {

        Route::livewire('/', ListEmpresaUser::class)->whereNumber('empresa_id')->name('list');
        Route::livewire('editar/{usuario_empresa_id}', SaveEmpresaUser::class)->whereNumber(['empresa_id', 'usuario_empresa_id'])->name('edit');
        Route::livewire('detalle/{usuario_empresa_id}', DetailEmpresaUser::class)->whereNumber(['empresa_id', 'usuario_empresa_id'])->name('detail');
        Route::livewire('permisos/{usuario_empresa_id}', PermissionEmpresaUser::class)->whereNumber(['empresa_id', 'usuario_empresa_id'])->name('permissions');

    });

    /* ----------------------------------------Clientes-------------------------------------- */

    Route::prefix('clientes')->name('clientes.')->group(function () {

        Route::livewire('/', ListCliente::class)->name('list');
        Route::livewire('editar/{user_id}', SaveCliente::class)->whereNumber('user_id')->name('edit');
        Route::livewire('detalle/{user_id}', DetailCliente::class)->whereNumber('user_id')->name('detail');

    });

    /* ----------------------------------------Legales-------------------------------------- */

    Route::prefix('legales')->name('legales.')->group(function () {

        Route::livewire('/', ListLegal::class)->name('list');
        Route::livewire('empresa/{empresa_id}', EmpresaLegal::class)->whereNumber('empresa_id')->name('detail');
        Route::get('empresa/{empresa_id}/documento/{documento_id}', [DocumentoLegalController::class, 'show'])->whereNumber('empresa_id')->whereNumber('documento_id')->name('file');

    });

    /* ----------------------------------------Rutas de viajes: supervision-------------------------------------- */

    Route::prefix('viajes')->name('viajes.')->group(function () {

        Route::livewire('/', ListViaje::class)->name('list');
        Route::livewire('detalle/{viaje_id}', DetailViaje::class)->whereNumber('viaje_id')->name('detail');

    });

    /* ----------------------------------------Salidas programadas: supervision-------------------------------------- */

    Route::prefix('programaciones')->name('programaciones.')->group(function () {

        Route::livewire('/', ListProgramacion::class)->name('list');
        Route::livewire('pasajeros/{programacion_id}', PassengerProgramacion::class)->whereNumber('programacion_id')->name('passengers');

    });

    /* ----------------------------------------Autobuses: supervision-------------------------------------- */

    Route::prefix('autobuses')->name('autobuses.')->group(function () {

        Route::livewire('/', ListAutobus::class)->name('list');
        Route::livewire('detalle/{autobus_id}', DetailAutobus::class)->whereNumber('autobus_id')->name('detail');

    });

    /* ----------------------------------------Reservas y ventas-------------------------------------- */

    Route::prefix('reservas')->name('reservas.')->group(function () {

        Route::livewire('/', ListReserva::class)->name('list');
        Route::livewire('detalle/{reserva_id}', DetailReserva::class)->whereNumber('reserva_id')->name('detail');

    });

    /* ----------------------------------------Pasajes-------------------------------------- */

    Route::prefix('pasajes')->name('pasajes.')->group(function () {

        Route::livewire('/', ListPasaje::class)->name('list');
        Route::livewire('detalle/{pasaje_id}', DetailPasaje::class)->whereNumber('pasaje_id')->name('detail');

    });

    /* ----------------------------------------Reembolsos-------------------------------------- */

    Route::prefix('reembolsos')->name('reembolsos.')->group(function () {
        Route::livewire('/', ListReembolso::class)->name('list');
        Route::livewire('detalle/{reembolso_id}', DetailReembolso::class)->whereNumber('reembolso_id')->name('detail');

    });

    /* ----------------------------------------Órdenes de cobro-------------------------------------- */

    Route::prefix('ordenes-cobro')->name('ordenes-cobro.')->group(function () {

        Route::livewire('/', ListOrdenCobro::class)->name('list');
        Route::livewire('detalle/{orden_cobro_id}', DetailOrdenCobro::class)->whereNumber('orden_cobro_id')->name('detail');

    });

    /* ----------------------------------------cupones de cupones-------------------------------------- */

    Route::prefix('cupones')->name('cupones.')->group(function () {

        Route::livewire('/', ListCampana::class)->name('list');
        Route::livewire('nuevo', SaveCampana::class)->name('add');
        Route::livewire('editar/{configuracion_cupon_id}', SaveCampana::class)->whereNumber('configuracion_cupon_id')->name('edit');
        Route::livewire('detalle/{configuracion_cupon_id}', DetailCampana::class)->whereNumber('configuracion_cupon_id')->name('detail');

    });

    /* ----------------------------------------Terminales-------------------------------------- */

    Route::prefix('terminales')->name('terminales.')->group(function () {

        Route::livewire('/', ListTerminal::class)->name('list');
        Route::livewire('nuevo', SaveTerminal::class)->name('add');
        Route::livewire('editar/{terminal_id}', SaveTerminal::class)->whereNumber('terminal_id')->name('edit');
    });

    /* ----------------------------------------Amenidades-------------------------------------- */

    Route::prefix('amenidades')->name('amenidades.')->group(function () {

        Route::livewire('/', ListAmenidad::class)->name('list');
        Route::livewire('nuevo', SaveAmenidad::class)->name('add');
        Route::livewire('editar/{amenidad_id}', SaveAmenidad::class)->whereNumber('amenidad_id')->name('edit');

    });

    /* ----------------------------------------Reportes-------------------------------------- */

    Route::prefix('reportes')->name('reportes.')->group(function () {

        Route::livewire('ventas', SalesReport::class)->name('sales');
        Route::livewire('empresas', CompaniesReport::class)->name('companies');

    });

    /* ----------------------------------------Solicitudes de empresas-------------------------------------- */

    Route::prefix('solicitudes')->name('solicitudes.')->group(function () {

        Route::livewire('/', ListSolicitud::class)->name('list');
        Route::livewire('detalle/{solicitud_id}', DetailSolicitud::class)->whereNumber('solicitud_id')->name('detail');

    });

    /* ----------------------------------------Mi cuenta-------------------------------------- */

    Route::prefix('mi-cuenta')->name('account.')->group(function () {

        Route::livewire('/', Profile::class)->name('profile');
        Route::livewire('password', Password::class)->name('password');

    });

});
