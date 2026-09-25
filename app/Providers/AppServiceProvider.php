<?php

namespace App\Providers;

use App\Listeners\UpdateSessionOnAuth;
use App\Support\ActionRateLimiter;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

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

        // Protege todas las peticiones de Livewire con un límite de 300 solicitudes por minuto por usuario autenticado o invitado.
        RateLimiter::for('livewire', function () {
            return Limit::perMinute(300)->by('livewire:'.ActionRateLimiter::identity());
        });

        // Aplica el limitador anterior al endpoint POST interno de Livewire y conserva la sesión, las cookies y la protección CSRF.
        Livewire::setUpdateRoute(function ($handle, $path) {
            return Route::post($path, $handle)->middleware([
                'web',
                'throttle:livewire',
            ]);
        });

        // Register session update listeners for login/logout so sessions table is linked to the authenticatable model
        Event::listen(Login::class, [UpdateSessionOnAuth::class, 'handle']);
        Event::listen(Logout::class, [UpdateSessionOnAuth::class, 'onLogout']);
    }
}
