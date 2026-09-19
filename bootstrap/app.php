<?php

use App\Http\Middleware\CheckPermission;
use App\Http\Middleware\RedirectIfUnauthenticated;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route; // Importante para definir el grupo de rutas

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        // Registramos el archivo personalizado cms.php
        then: function (): void {
            Route::middleware('web')
                ->prefix('admin')
                ->name('admin.')
                ->group(base_path('routes/admin.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->group('check.permisos', [
            CheckPermission::class,
        ]);

        // Configurar redirección para invitados (no autenticados)
        $middleware->redirectTo(
            guests: function (Request $request) {

            if ($request->is('admin') || $request->is('admin/*')) {
                    return route('admin.login');
                }

                return route('admin.login');
            }
        );

        $middleware->alias([
            'auth.site' =>RedirectIfUnauthenticated::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
