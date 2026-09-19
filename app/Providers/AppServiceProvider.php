<?php

namespace App\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use App\Listeners\UpdateSessionOnAuth;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register session update listeners for login/logout so sessions table is linked to the authenticatable model
        Event::listen(Login::class, [UpdateSessionOnAuth::class, 'handle']);
        Event::listen(Logout::class, [UpdateSessionOnAuth::class, 'onLogout']);
    }
}
