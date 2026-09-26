<?php

namespace App\Support;

use App\Models\Reserva;
use App\Models\TipoCambio;

class ConversorMoneda
{
    public static function aBolivares(string|int|float|null $montoUsd, ?TipoCambio $tipoCambio): ?string
    {
        if ($montoUsd === null || ! $tipoCambio) {
            return null;
        }

        return bcmul((string) $montoUsd, $tipoCambio->valor_usd, 2);
    }

    public static function ordenes(iterable $ordenes): array
    {
        $ordenes = collect($ordenes);
        $reservasIncluidas = $ordenes->flatMap(
            fn ($orden) => collect($orden->reservas_incluidas ?? [])->pluck('reserva_id'),
        )->filter()->unique()->values();

        $reservas = Reserva::query()
            ->with('tipoCambio')
            ->whereKey($reservasIncluidas)
            ->get()
            ->keyBy('id');

        return $ordenes->mapWithKeys(function ($orden) use ($reservas) {
            $totalBs = '0.00';
            $porReserva = [];

            foreach ($orden->reservas_incluidas ?? [] as $incluida) {
                $reservaId = (int) ($incluida['reserva_id'] ?? 0);
                $montoBs = self::aBolivares(
                    $incluida['tasa_servicio'] ?? null,
                    $reservas->get($reservaId)?->tipoCambio,
                );

                $porReserva[$reservaId] = $montoBs;

                if ($montoBs !== null) {
                    $totalBs = bcadd($totalBs, $montoBs, 2);
                }
            }

            return [$orden->id => [
                'total_bs' => $totalBs,
                'reservas_bs' => $porReserva,
            ]];
        })->all();
    }
}
