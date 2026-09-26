<?php

namespace App\Services;

use App\Models\TipoCambio;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class TipoCambioService
{
    public function actualizar(): TipoCambio
    {
        $respuesta = $this->cliente()
            ->get(config('services.bcv.endpoint'))
            ->throw()
            ->json();

        $usd = data_get($respuesta, 'USD');
        $eur = data_get($respuesta, 'EUR');

        if (! is_numeric($usd) || ! is_numeric($eur) || (float) $usd <= 0 || (float) $eur <= 0) {
            throw new RuntimeException('La respuesta de la API BCV no contiene tasas USD y EUR válidas.');
        }

        return TipoCambio::create([
            'valor_usd' => $this->primerosDosDecimales($usd),
            'valor_eur' => $this->primerosDosDecimales($eur),
            'valor' => 1,
            'timestamp' => now(),
        ]);
    }

    private function primerosDosDecimales(int|float|string $valor): string
    {
        return bcdiv((string) $valor, '1', 2);
    }

    private function cliente(): PendingRequest
    {
        return Http::acceptJson()
            ->timeout((int) config('services.bcv.timeout', 15))
            ->retry(3, 500, throw: false);
    }
}
