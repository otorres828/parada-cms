<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Livewire Classes
|--------------------------------------------------------------------------
*/

/* ------------------------------Autenticacion------------------------------------ */
use App\Livewire\Empresas\Auth\Login;



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
