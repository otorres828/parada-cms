<?php

use App\Livewire\Home;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Livewire Classes
|--------------------------------------------------------------------------
*/

Route::livewire('/', Home::class)->name('home');
