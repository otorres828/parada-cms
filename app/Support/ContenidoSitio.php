<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;

class ContenidoSitio
{
    public const PAGINAS = [
        'sobre-nosotros' => 'Sobre nosotros',
        'politicas-privacidad' => 'Políticas de privacidad',
        'politicas-cookies' => 'Políticas de cookies',
        'terminos-condiciones' => 'Términos y condiciones',
    ];

    public static function leer(string $pagina): array
    {
        abort_unless(isset(self::PAGINAS[$pagina]), 404);
        $disk = Storage::disk('public');
        $path = 'json/'.$pagina.'.json';

        return $disk->exists($path)
            ? json_decode($disk->get($path), true, 512, JSON_THROW_ON_ERROR)
            : ['titulo' => self::PAGINAS[$pagina], 'contenido' => '', 'actualizado_en' => null];
    }

    public static function guardar(string $pagina, string $contenido): void
    {
        abort_unless(isset(self::PAGINAS[$pagina]), 404);
        $data = ['titulo' => self::PAGINAS[$pagina], 'contenido' => $contenido, 'actualizado_en' => now()->toIso8601String()];
        $path = Storage::disk('public')->path('json/'.$pagina.'.json');
        \Illuminate\Support\Facades\File::ensureDirectoryExists(dirname($path));
        \Illuminate\Support\Facades\File::replace($path, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));
    }
}

