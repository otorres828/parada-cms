<?php

namespace App\Services;

use App\Models\Pasaje;
use App\Models\Reserva;
use Carbon\Carbon;
use RuntimeException;

class GoogleWalletService
{
    public static function generarUrl(Pasaje $pasaje): string
    {
        self::exigirConfiguracion();

        $pasaje->loadMissing([
            'reserva.origenTerminal',
            'reserva.destinoTerminal',
            'reserva.tramoPrecio',
            'reserva.programacion.transporte',
            'reserva.programacion.viaje.empresa',
        ]);

        $reserva = $pasaje->reserva;

        if ($reserva->estado_pago !== Reserva::ESTADO_PAGO_PAGADO) {
            throw new RuntimeException('Solo los pasajes de reservas pagadas se pueden añadir a Google Wallet.');
        }

        $credenciales = self::credenciales();
        $salida = $reserva->tramoPrecio->getSalida();
        $llegada = $reserva->tramoPrecio->getLlegada();
        $empresa = $reserva->programacion->viaje->empresa;
        $transporte = $reserva->programacion->transporte;

        $objeto = [
            'id' => config('services.google_wallet.issuer_id').'.pasaje_'.$pasaje->id,
            'classId' => config('services.google_wallet.class_id'),
            'state' => 'ACTIVE',
            'cardTitle' => [
                'defaultValue' => [
                    'language' => 'es',
                    'value' => 'Pasaje Rodando',
                ],
            ],
            'header' => [
                'defaultValue' => [
                    'language' => 'es',
                    'value' => $reserva->origenTerminal->nombre.' → '.$reserva->destinoTerminal->nombre,
                ],
            ],
            'subheader' => [
                'defaultValue' => [
                    'language' => 'es',
                    'value' => $pasaje->viajero_nombre_completo,
                ],
            ],
            'barcode' => [
                'type' => 'QR_CODE',
                'value' => $pasaje->localizador,
                'alternateText' => $pasaje->localizador,
            ],
            'textModulesData' => [
                [
                    'id' => 'salida',
                    'header' => 'Salida',
                    'body' => self::formatearFecha($salida),
                ],
                [
                    'id' => 'llegada',
                    'header' => 'Llegada',
                    'body' => self::formatearFecha($llegada),
                ],
                [
                    'id' => 'asiento',
                    'header' => 'Asiento',
                    'body' => $pasaje->numero_asiento ?? 'Sin asiento',
                ],
                [
                    'id' => 'empresa',
                    'header' => 'Empresa',
                    'body' => $empresa->nombre,
                ],
                [
                    'id' => 'transporte',
                    'header' => 'Transporte',
                    'body' => trim($transporte->modelo.' '.$transporte->placa),
                ],
            ],
        ];

        if ($salida instanceof Carbon && $llegada instanceof Carbon) {
            $objeto['validTimeInterval'] = [
                'start' => [
                    'date' => $salida->toAtomString(),
                ],
                'end' => [
                    'date' => $llegada->toAtomString(),
                ],
            ];
        }

        $payload = [
            'iss' => $credenciales['client_email'],
            'aud' => 'google',
            'typ' => 'savetowallet',
            'iat' => now()->timestamp,
            'origins' => array_filter([
                config('services.google_wallet.origin'),
            ]),
            'payload' => [
                'genericObjects' => [
                    $objeto,
                ],
            ],
        ];

        $jwt = self::firmarJwt($payload, $credenciales['private_key']);

        return 'https://pay.google.com/gp/v/save/'.$jwt;
    }

    public static function estaConfigurado(): bool
    {
        return self::erroresConfiguracion() === [];
    }

    public static function erroresConfiguracion(): array
    {
        $errores = [];

        foreach (['issuer_id', 'class_id'] as $campo) {
            if (blank(config('services.google_wallet.'.$campo))) {
                $errores[] = 'Falta GOOGLE_WALLET_'.strtoupper($campo).'.';
            }
        }

        if (blank(env('GOOGLE_WALLET_CREDENTIALS_BASE64'))) {
            $errores[] = 'Falta la variable de entorno GOOGLE_WALLET_CREDENTIALS_BASE64.';
        }

        return $errores;
    }

    protected static function exigirConfiguracion(): void
    {
        $errores = self::erroresConfiguracion();

        if ($errores !== []) {
            throw new RuntimeException(implode(' ', $errores));
        }
    }

    protected static function credenciales(): array
    {
        $base64 = env('GOOGLE_WALLET_CREDENTIALS_BASE64');

        if (blank($base64)) {
            throw new RuntimeException('No se encontró la variable de entorno GOOGLE_WALLET_CREDENTIALS_BASE64.');
        }

        $jsonString = base64_decode($base64);
        $credenciales = json_decode($jsonString, true);

        if (
            ! is_array($credenciales)
            || blank($credenciales['client_email'] ?? null)
            || blank($credenciales['private_key'] ?? null)
        ) {
            throw new RuntimeException('Las credenciales de Google Wallet en Base64 no son válidas.');
        }

        return $credenciales;
    }

    protected static function firmarJwt(array $payload, string $privateKey): string
    {
        $header = self::base64Url(json_encode([
            'alg' => 'RS256',
            'typ' => 'JWT',
        ], JSON_THROW_ON_ERROR));

        $body = self::base64Url(json_encode($payload, JSON_THROW_ON_ERROR));
        $contenido = $header.'.'.$body;

        if (! openssl_sign($contenido, $firma, $privateKey, OPENSSL_ALGO_SHA256)) {
            throw new RuntimeException('No se pudo firmar el pase de Google Wallet.');
        }

        return $contenido.'.'.self::base64Url($firma);
    }

    protected static function base64Url(string $valor): string
    {
        return rtrim(strtr(base64_encode($valor), '+/', '-_'), '=');
    }

    protected static function formatearFecha(?Carbon $fecha): string
    {
        return $fecha?->format('d/m/Y H:i') ?? 'Pendiente de configurar';
    }
}