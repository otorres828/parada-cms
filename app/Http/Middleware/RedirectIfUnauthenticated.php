<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfUnauthenticated
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Si el usuario NO está autenticado, lo mandamos al home
        if (!Auth::check()) {
            return redirect()->route('home'); // Asegúrate de tener una ruta nombrada 'home'
        }

        return $next($request);
    }
}
