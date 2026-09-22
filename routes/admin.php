<?php

/*
|--------------------------------------------------------------------------
| Livewire Classes
|--------------------------------------------------------------------------
*/

/*------------------------------Autenticacion-----------------------------------*/
use App\Livewire\Admin\Auth\Login;
/*------------------------------Dashboard---------------------------------------*/
use App\Livewire\Admin\Dashboard;
/*------------------------------Perfil------------------------------------------*/
use App\Livewire\Admin\Account\Profile;
use App\Livewire\Admin\Account\Password;
/*------------------------------Administradores----------------------------------*/
use App\Livewire\Admin\Admins\ListAdmin;
use App\Livewire\Admin\Admins\SaveAdmin;
/*------------------------------Tasa de Servicio---------------------------------*/
use App\Livewire\Admin\TasasServicio\ListTasaServicio;
use App\Livewire\Admin\TasasServicio\SaveTasaServicio;
/*------------------------------Auditoria----------------------------------------*/
use App\Livewire\Admin\Auditoria\ListAudit;
use App\Livewire\Admin\Auditoria\DetailAudit;
/*------------------------------Empresas-----------------------------------------*/
use App\Livewire\Admin\Empresas\ListEmpresa;
use App\Livewire\Admin\Empresas\SaveEmpresa;
use App\Livewire\Admin\Empresas\DetailEmpresa;
/*------------------------------Usuarios Empresas--------------------------------*/
use App\Livewire\Admin\EmpresaUsers\ListEmpresaUser;
use App\Livewire\Admin\EmpresaUsers\SaveEmpresaUser;
use App\Livewire\Admin\EmpresaUsers\DetailEmpresaUser;
use App\Livewire\Admin\EmpresaUsers\PermissionEmpresaUser;
/*------------------------------Clientes------------------------------------------*/
use App\Livewire\Admin\Clientes\ListCliente;
use App\Livewire\Admin\Clientes\DetailCliente;
use App\Livewire\Admin\Clientes\SaveCliente;
/*------------------------------Legales------------------------------------------*/
use App\Livewire\Admin\Legales\ListLegal;
use App\Livewire\Admin\Legales\EmpresaLegal;
use App\Http\Controllers\Admin\DocumentoLegalController;
/*------------------------------Rutas de viaje------------------------------------*/
use App\Livewire\Admin\Viajes\ListViaje;
use App\Livewire\Admin\Viajes\DetailViaje;
/*------------------------------Programaciones------------------------------------*/
use App\Livewire\Admin\Programaciones\ListProgramacion;
use App\Livewire\Admin\Programaciones\PassengerProgramacion;
/*------------------------------Autobuses-----------------------------------------*/
use App\Livewire\Admin\Autobuses\ListAutobus;
use App\Livewire\Admin\Autobuses\DetailAutobus;
/*------------------------------Reservas------------------------------------------*/
use App\Livewire\Admin\Reservas\ListReserva;
use App\Livewire\Admin\Reservas\DetailReserva;
/*------------------------------Pasajes-------------------------------------------*/
use App\Livewire\Admin\Pasajes\ListPasaje;
use App\Livewire\Admin\Pasajes\DetailPasaje;

use App\Livewire\Admin\Reembolsos\ListReembolso;
use App\Livewire\Admin\Reembolsos\DetailReembolso;
use App\Livewire\Admin\Reembolsos\ReviewReembolso;
use App\Livewire\Admin\Cupones\ListCampana;
use App\Livewire\Admin\Cupones\SaveCampana;
use App\Livewire\Admin\Cupones\DetailCampana;
use App\Livewire\Admin\Terminales\ListTerminal;
use App\Livewire\Admin\Terminales\SaveTerminal;
use App\Livewire\Admin\Terminales\DetailTerminal;
use App\Livewire\Admin\Amenidades\ListAmenidad;
use App\Livewire\Admin\Amenidades\SaveAmenidad;
use App\Livewire\Admin\Reportes\SalesReport;
use App\Livewire\Admin\Reportes\CompaniesReport;
use App\Livewire\Admin\Settings\GeneralSettings;




/*
|--------------------------------------------------------------------------
| Misc Classes
|--------------------------------------------------------------------------
*/

use Illuminate\Support\Facades\Route;

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

    /*----------------------------------------Administradores--------------------------------------*/

    Route::prefix('administradores')->name('admins.')->group(function () {

        Route::livewire('/', ListAdmin::class)->name('list');
        Route::livewire('nuevo', SaveAdmin::class)->name('add');
        Route::livewire('editar/{admin_id}', SaveAdmin::class)->whereNumber('admin_id')->name('edit');

    });

    /*----------------------------------------Tasas de servicio--------------------------------------*/

    Route::prefix('tasas-servicio')->name('tasas-servicio.')->group(function () {
        
        Route::livewire('/', ListTasaServicio::class)->name('list');
        Route::livewire('nuevo', SaveTasaServicio::class)->name('add');
        Route::livewire('editar/{tasa_servicio_id}', SaveTasaServicio::class)->whereNumber('tasa_servicio_id')->name('edit');

    });

    /*----------------------------------------Auditoria--------------------------------------*/

    Route::prefix('auditoria')->name('auditoria.')->group(function () {

        Route::livewire('/', ListAudit::class)->name('list');
        Route::livewire('detalle/{audit_id}', DetailAudit::class)->whereNumber('audit_id')->name('detail');

    });

    /*----------------------------------------Empresas--------------------------------------*/

    Route::prefix('empresas')->name('empresas.')->group(function () {

        Route::livewire('/', ListEmpresa::class)->name('list');
        Route::livewire('nuevo', SaveEmpresa::class)->name('add');
        Route::livewire('editar/{empresa_id}', SaveEmpresa::class)->whereNumber('empresa_id')->name('edit');
        Route::livewire('detalle/{empresa_id}', DetailEmpresa::class)->whereNumber('empresa_id')->name('detail');

    });

    /*----------------------------------------Usuarios de empresa--------------------------------------*/

    Route::prefix('empresas/{empresa_id}/usuarios')->name('empresas.users.')->group(function () {

        Route::livewire('/', ListEmpresaUser::class)->whereNumber('empresa_id')->name('list');
        Route::livewire('nuevo', SaveEmpresaUser::class)->whereNumber('empresa_id')->name('add');
        Route::livewire('editar/{usuario_empresa_id}', SaveEmpresaUser::class)->whereNumber(['empresa_id', 'usuario_empresa_id'])->name('edit');
        Route::livewire('detalle/{usuario_empresa_id}', DetailEmpresaUser::class)->whereNumber(['empresa_id', 'usuario_empresa_id'])->name('detail');
        Route::livewire('permisos/{usuario_empresa_id}', PermissionEmpresaUser::class)->whereNumber(['empresa_id', 'usuario_empresa_id'])->name('permissions');

    });

    /*----------------------------------------Clientes--------------------------------------*/

    Route::prefix('clientes')->name('clientes.')->group(function () {

        Route::livewire('/', ListCliente::class)->name('list');
        Route::livewire('editar/{user_id}', SaveCliente::class)->whereNumber('user_id')->name('edit');
        Route::livewire('detalle/{user_id}', DetailCliente::class)->whereNumber('user_id')->name('detail');

    });

    /*----------------------------------------Legales--------------------------------------*/

    Route::prefix('legales')->name('legales.')->group(function () {

        Route::livewire('/', ListLegal::class)->name('list');
        Route::livewire('empresa/{empresa_id}', EmpresaLegal::class)->whereNumber('empresa_id')->name('detail');
        Route::get('empresa/{empresa_id}/documento/{documento_id}', [DocumentoLegalController::class, 'show'])->whereNumber('empresa_id')->whereNumber('documento_id')->name('file');
        
    });

    /*----------------------------------------Rutas de viajes: supervision--------------------------------------*/

    Route::prefix('viajes')->name('viajes.')->group(function () {

        Route::livewire('/', ListViaje::class)->name('list');
        Route::livewire('detalle/{viaje_id}', DetailViaje::class)->whereNumber('viaje_id')->name('detail');

    });

    /*----------------------------------------Salidas programadas: supervision--------------------------------------*/

    Route::prefix('programaciones')->name('programaciones.')->group(function () {

        Route::livewire('/', ListProgramacion::class)->name('list');
        Route::livewire('pasajeros/{programacion_id}', PassengerProgramacion::class)->whereNumber('programacion_id')->name('passengers');

    });

    /*----------------------------------------Autobuses: supervision--------------------------------------*/

    Route::prefix('autobuses')->name('autobuses.')->group(function () {

        Route::livewire('/', ListAutobus::class)->name('list');
        Route::livewire('detalle/{autobus_id}', DetailAutobus::class)->whereNumber('autobus_id')->name('detail');

    });

    /*----------------------------------------Reservas y ventas--------------------------------------*/

    Route::prefix('reservas')->name('reservas.')->group(function () {

        Route::livewire('/', ListReserva::class)->name('list');
        Route::livewire('detalle/{reserva_id}', DetailReserva::class)->whereNumber('reserva_id')->name('detail');

    });

    /*----------------------------------------Pasajes--------------------------------------*/

    Route::prefix('pasajes')->name('pasajes.')->group(function () {

        Route::livewire('/', ListPasaje::class)->name('list');
        Route::livewire('detalle/{pasaje_id}', DetailPasaje::class)->whereNumber('pasaje_id')->name('detail');

    });



    /*----------------------------------------Reembolsos--------------------------------------*/

    Route::prefix('reembolsos')->name('reembolsos.')->group(function () {

        Route::livewire('nuevo', \App\Livewire\Admin\Reembolsos\SaveReembolso::class)->name('add');

        Route::livewire('/', ListReembolso::class)->name('list');
        Route::livewire('detalle/{reembolso_id}', DetailReembolso::class)->whereNumber('reembolso_id')->name('detail');
        Route::livewire('revision/{reembolso_id}', ReviewReembolso::class)->whereNumber('reembolso_id')->name('review');

    });


    /*----------------------------------------cupones de cupones--------------------------------------*/

    Route::prefix('cupones')->name('cupones.')->group(function () {

        Route::livewire('/', ListCampana::class)->name('list');
        Route::livewire('nuevo', SaveCampana::class)->name('add');
        Route::livewire('editar/{configuracion_cupon_id}', SaveCampana::class)->whereNumber('configuracion_cupon_id')->name('edit');
        Route::livewire('detalle/{configuracion_cupon_id}', DetailCampana::class)->whereNumber('configuracion_cupon_id')->name('detail');

    });

    /*----------------------------------------Autobuses--------------------------------------*/

/*----------------------------------------Terminales--------------------------------------*/

    Route::prefix('terminales')->name('terminales.')->group(function () {

        Route::livewire('/', ListTerminal::class)->name('list');
        Route::livewire('nuevo', SaveTerminal::class)->name('add');
        Route::livewire('editar/{terminal_id}', SaveTerminal::class)->whereNumber('terminal_id')->name('edit');
        Route::livewire('detalle/{terminal_id}', DetailTerminal::class)->whereNumber('terminal_id')->name('detail');

    });

    /*----------------------------------------Amenidades--------------------------------------*/

    Route::prefix('amenidades')->name('amenidades.')->group(function () {

        Route::livewire('/', ListAmenidad::class)->name('list');
        Route::livewire('nuevo', SaveAmenidad::class)->name('add');
        Route::livewire('editar/{amenidad_id}', SaveAmenidad::class)->whereNumber('amenidad_id')->name('edit');

    });

    /*----------------------------------------Reportes--------------------------------------*/

    Route::prefix('reportes')->name('reportes.')->group(function () {

        Route::livewire('ventas', SalesReport::class)->name('sales');
        Route::livewire('empresas', CompaniesReport::class)->name('companies');

    });

    /*----------------------------------------Configuracion de la plataforma--------------------------------------*/

    Route::prefix('configuracion')->name('settings.')->group(function () {

        Route::livewire('/', GeneralSettings::class)->name('general');

    });



    /*----------------------------------------Mi cuenta--------------------------------------*/

    Route::prefix('mi-cuenta')->name('account.')->group(function () {

        Route::livewire('/', Profile::class)->name('profile');
        Route::livewire('password', Password::class)->name('password');

    });

});
