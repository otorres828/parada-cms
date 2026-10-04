<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Livewire Classes
|--------------------------------------------------------------------------
*/

/* ------------------------------Autenticacion------------------------------------ */
use App\Livewire\Empresas\Auth\Login;
/* ------------------------------Dashboard---------------------------------------- */
use App\Livewire\Empresas\Dashboard;
/* ------------------------------Usuarios------------------------------------ */
use App\Livewire\Empresas\Usuarios\ListUsuario;
/* ------------------------------Políticas de embarque------------------------------------ */
use App\Livewire\Empresas\PoliticasEmbarque\SavePoliticaEmbarque;
/* ------------------------------Datos Bancarios------------------------------------ */
use App\Livewire\Empresas\DatosBancarios\ListDatoBancario;
use App\Livewire\Empresas\DatosBancarios\SaveDatoBancario;
/* ------------------------------Rutas de viaje------------------------------------ */
use App\Livewire\Empresas\Viajes\ListViaje;
use App\Livewire\Empresas\Viajes\SaveViaje;
use App\Livewire\Empresas\Viajes\DetailViaje;
/* ------------------------------Programaciones------------------------------------ */
use App\Livewire\Empresas\Programaciones\ListProgramacion;
use App\Livewire\Empresas\Programaciones\DetailProgramacion;
use App\Livewire\Empresas\Programaciones\SaveProgramacion;
/* ------------------------------Transportes------------------------------------ */
use App\Livewire\Empresas\Transportes\ListTransporte;
use App\Livewire\Empresas\Transportes\SaveTransporte;
/* ------------------------------Reservas------------------------------------ */
use App\Livewire\Empresas\Reservas\ListReserva;
use App\Livewire\Empresas\Reservas\DetailReserva;
use App\Livewire\Empresas\Reservas\SaveReserva;
/* ------------------------------Pasajes------------------------------------ */
use App\Livewire\Empresas\Pasajes\ListPasaje;
use App\Livewire\Empresas\Pasajes\DetailPasaje;
/* ------------------------------Validación de pagos------------------------------------ */
use App\Livewire\Empresas\ValidacionPagos\ListValidacionPago;
/* ------------------------------Reembolsos------------------------------------ */
use App\Livewire\Empresas\Reembolsos\ListReembolso;
/* ------------------------------Reprogramaciones------------------------------------ */
use App\Livewire\Empresas\Reprogramaciones\ListReprogramacion;
/* ------------------------------Órdenes de cobro------------------------------------ */
use App\Livewire\Empresas\OrdenesCobro\ListOrdenCobro;
/* ------------------------------Cupones------------------------------------ */
use App\Livewire\Empresas\Cupones\ListCampana;
/* ------------------------------Ventas------------------------------------ */
use App\Livewire\Empresas\Reportes\SalesReport;
/* ------------------------------Rutas------------------------------------ */
use App\Livewire\Empresas\Reportes\RoutesReport;
/* ------------------------------Perfil------------------------------------------- */
use App\Livewire\Empresas\Account\Profile;
use App\Livewire\Empresas\Account\Password;


/*
| Authentication Routes
|--------------------------------------------------------------------------
| bootstrap/app.php aplica el prefijo /empresa y el nombre empresas.
*/

Route::livewire('/', Login::class)->name('login');

Route::post('logout', [Login::class, 'logout'])
    ->middleware('auth:empresa')
    ->name('auth.logout');

/*
|--------------------------------------------------------------------------
| Protected Routes
|--------------------------------------------------------------------------
*/

Route::group(['middleware' => ['auth:empresa', 'check.permisos.empresa']], function () {

    Route::livewire('dashboard', Dashboard::class)->name('dashboard');


    /* ----------------------------------------Administración---------------------------------------- */

    Route::prefix('administracion')->group(function () {

        /* ----------------------------------------Usuarios---------------------------------------- */

        Route::prefix('usuarios')->name('usuarios.')->group(function () {

            Route::livewire('/', ListUsuario::class)->name('list');

        });

        /* ----------------------------------------Políticas de embarque---------------------------------------- */

        Route::prefix('politicas-embarque')->name('politicas-embarque.')->group(function () {

            Route::livewire('/', SavePoliticaEmbarque::class)->name('edit');

        });

        /* ----------------------------------------Datos Bancarios---------------------------------------- */

        Route::prefix('datos-bancarios')->name('datos-bancarios.')->group(function () {
            Route::livewire('/', ListDatoBancario::class)->name('list');
            Route::livewire('nuevo', SaveDatoBancario::class)->name('add');
            Route::livewire('editar/{cuenta_id}', SaveDatoBancario::class)->whereNumber('cuenta_id')->name('edit');
        });

    });

    /* ----------------------------------------Operación de viajes---------------------------------------- */

    Route::prefix('operaciones')->group(function () {

        /* ----------------------------------------Rutas de viaje---------------------------------------- */

        Route::prefix('viajes')->name('viajes.')->group(function () {

            Route::livewire('/', ListViaje::class)->name('list');
            Route::livewire('detalle/{viaje_id}', DetailViaje::class)->whereNumber('viaje_id')->name('detail');
            Route::livewire('agregar', SaveViaje::class)->name('add');
            Route::livewire('editar/{viaje_id}', SaveViaje::class)->whereNumber('viaje_id')->name('edit');

        });

        /* ----------------------------------------Programaciones---------------------------------------- */

        Route::prefix('programaciones')->name('programaciones.')->group(function () {

            Route::livewire('/', ListProgramacion::class)->name('list');
            Route::livewire('detalle/{programacion_id}', DetailProgramacion::class)->whereNumber('programacion_id')->name('detail');
            Route::livewire('agregar', SaveProgramacion::class)->name('add');
            Route::livewire('editar/{programacion_id}', SaveProgramacion::class)->whereNumber('programacion_id')->name('edit');

        });

        /* ----------------------------------------Transportes---------------------------------------- */

        Route::prefix('transportes')->name('transportes.')->group(function () {

            Route::livewire('/', ListTransporte::class)->name('list');
            Route::livewire('agregar', SaveTransporte::class)->name('add');
            Route::livewire('editar/{transporte_id}', SaveTransporte::class)->whereNumber('transporte_id')->name('edit');

        });

    });

    /* ----------------------------------------Ventas y finanzas---------------------------------------- */

    Route::prefix('finanzas')->group(function () {

        /* ----------------------------------------Reservas---------------------------------------- */

        Route::prefix('reservas')->name('reservas.')->group(function () {

            Route::livewire('/', ListReserva::class)->name('list');
            Route::livewire('detalle/{reserva_id}', DetailReserva::class)->whereNumber('reserva_id')->name('detail');
            Route::livewire('agregar', SaveReserva::class)->whereNumber('reserva_id')->name('add');

        });

        /* ----------------------------------------Pasajes---------------------------------------- */

        Route::prefix('pasajes')->name('pasajes.')->group(function () {

            Route::livewire('/', ListPasaje::class)->name('list');
            Route::livewire('detalle/{pasaje_id}', DetailPasaje::class)->whereNumber('pasaje_id')->name('detail');

        });

        /* ----------------------------------------Validación de pagos---------------------------------------- */

        Route::prefix('validacion-pagos')->name('validacion-pagos.')->group(function () {

            Route::livewire('/', ListValidacionPago::class)->name('list');

        });

        /* ----------------------------------------Reembolsos---------------------------------------- */

        Route::prefix('reembolsos')->name('reembolsos.')->group(function () {

            Route::livewire('/', ListReembolso::class)->name('list');

        });

        /* ----------------------------------------Reprogramaciones---------------------------------------- */

        Route::prefix('reprogramaciones')->name('reprogramaciones.')->group(function () {

            Route::livewire('/', ListReprogramacion::class)->name('list');

        });

    });

    /* ----------------------------------------Cobranza---------------------------------------- */

    Route::prefix('cobranza')->group(function () {

        /* ----------------------------------------Órdenes de cobro---------------------------------------- */

        Route::prefix('ordenes-cobro')->name('ordenes-cobro.')->group(function () {

            Route::livewire('/', ListOrdenCobro::class)->name('list');

        });

    });

    /* ----------------------------------------Promociones---------------------------------------- */

    Route::prefix('promociones')->group(function () {

        /* ----------------------------------------Cupones---------------------------------------- */

        Route::prefix('cupones')->name('cupones.')->group(function () {

            Route::livewire('/', ListCampana::class)->name('list');

        });

    });

    /* ----------------------------------------Reportes---------------------------------------- */

    Route::prefix('reportes')->name('reportes.')->group(function () {

        Route::livewire('ventas', SalesReport::class)->name('ventas');

        Route::livewire('rutas', RoutesReport::class)->name('rutas');

    });
    /* ---------------------------------------------MI CUENTA---------------------------------------------------------- */

    Route::prefix('mi-cuenta')->name('account.')->group(function () {

        Route::livewire('/', Profile::class)->name('profile');
        Route::livewire('password', Password::class)->name('password');

    });

});