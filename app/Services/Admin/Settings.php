<?php

namespace App\Services\Admin;

use App\Models\Configuracion;

class Settings
{
    public static function get(string $group, string $key, mixed $default = null): mixed
    {
        return (Configuracion::where('grupo', $group)->value('valores') ?? [])[$key] ?? $default;
    }
}
