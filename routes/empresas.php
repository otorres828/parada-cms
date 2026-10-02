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
/* ------------------------------Perfil------------------------------------------- */
use App\Livewire\Empresas\Account\Profile;
use App\Livewire\Empresas\Account\Password;


/*
| Authentication Routes
|--------------------------------------------------------------------------
| bootstrap/app.php aplica el prefijo /empresas y el nombre empresas.
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

Route::group(['middleware' => ['auth:empresa', 'check.permisos.empresas']], function () {

    Route::livewire('dashboard', Dashboard::class)->name('dashboard');

    /* --------------------------------------------ADMINISTRACION------------------------------------------------------- */

    Route::prefix('administracion')->group(function(){

        /* ----------------------------------------Usuarios------------------------------------------------------- */
    


    });

  

    /* ---------------------------------------------MI CUENTA---------------------------------------------------------- */

    Route::prefix('mi-cuenta')->name('account.')->group(function () {

        Route::livewire('/', Profile::class)->name('profile');
        Route::livewire('password', Password::class)->name('password');

    });

});