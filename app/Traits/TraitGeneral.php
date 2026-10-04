<?php

namespace App\Traits;

use Carbon\Carbon;

trait TraitGeneral
{
    public static function getMinFilterDate(): string
    {
        return '2000-01-01';
    }

    public static function getMaxFilterDate(): string
    {
        return now()->addYears(10)->endOfYear()->toDateString();
    }

    public static function getDefaultDesde(): string
    {
        return now()->subWeek()->toDateString();
    }

    public static function getDefaultHasta(): string
    {
        return now()->addDays(30)->toDateString();
    }

    public static function date(string $value, string $attribute = 'date_from'): string
    {
        foreach (['Y-m-d', 'd-m-Y'] as $format) {
            try {
                $date = Carbon::createFromFormat('!'.$format, $value);
                if ($date && $date->format($format) === $value) {
                    $min = Carbon::createFromFormat('!Y-m-d', self::getMinFilterDate());
                    $max = Carbon::createFromFormat('!Y-m-d', self::getMaxFilterDate());

                    if ($date->lessThan($min) || $date->greaterThan($max)) {
                        return self::getDefaultFilterDate($attribute);
                    }

                    return $date->toDateString();
                }
            } catch (\Throwable) {
            }
        }

        return self::getDefaultFilterDate($attribute);
    }

    private static function getDefaultFilterDate(string $attribute): string
    {
        return $attribute === 'date_to'
            ? self::getDefaultHasta()
            : self::getDefaultDesde();
    }

    public function generarLocalizador(int $length = 6): string
    {
        return substr(str_shuffle('23456789ABCDEFGHJKLMNPQRSTUVWXYZ'), 0, $length);
    }
}
