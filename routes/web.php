<?php

use App\Livewire\Home;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Livewire Classes
|--------------------------------------------------------------------------
*/

/* ------------------------------Authentication---------------------------- */

Route::livewire('/', Home::class)->name('home');
