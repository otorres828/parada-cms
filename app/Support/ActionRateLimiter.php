<?php

namespace App\Support;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;

class ActionRateLimiter
{
    public static function validarRateLimit(): void
    {
        self::ensure('solicitud-empresa:10-minutos', 3, 600);
        self::ensure('solicitud-empresa:dia', 10, 86400);
    }

    public static function ensure(string $action, int $maxAttempts, int $decaySeconds): void
    {
        $key = 'action:'.$action.':'.self::identity();

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            $seconds = RateLimiter::availableIn($key);

            abort(429, 'Has realizado demasiadas solicitudes. Intenta nuevamente en '.$seconds.' segundos.');
        }

        RateLimiter::hit($key, $decaySeconds);
    }

    public static function identity(): string
    {
        if (Auth::guard('admin')->check()) {
            return 'admin:'.Auth::guard('admin')->id();
        }

        if (Auth::guard('web')->check()) {
            return 'cliente:'.Auth::guard('web')->id();
        }

        return 'guest:'.request()->ip();
    }
}
