<?php

namespace App\Support;

class PersonalData
{
    public static function hashDocumento(?string $documento): ?string
    {
        return self::hash(self::normalizarDocumento($documento));
    }

    public static function hashTelefono(?string $telefono): ?string
    {
        return self::hash(self::normalizarTelefono($telefono));
    }

    public static function normalizarDocumento(?string $documento): ?string
    {
        if ($documento === null || trim($documento) === '') {
            return null;
        }

        return strtoupper((string) preg_replace('/[^A-Z0-9]/i', '', $documento));
    }

    public static function normalizarTelefono(?string $telefono): ?string
    {
        if ($telefono === null || trim($telefono) === '') {
            return null;
        }

        return (string) preg_replace('/\D+/', '', $telefono);
    }

    private static function hash(?string $valor): ?string
    {
        if ($valor === null || $valor === '') {
            return null;
        }

        return hash_hmac('sha256', $valor, (string) config('app.pii_hash_key'));
    }
}
