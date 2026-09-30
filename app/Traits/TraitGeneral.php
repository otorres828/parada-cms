<?php

namespace App\Traits;

use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

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
        return now()->toDateString();
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
                        throw ValidationException::withMessages([
                            $attribute => 'La fecha debe estar entre '.self::getMinFilterDate().' y '.self::getMaxFilterDate().'.',
                        ]);
                    }

                    return $date->toDateString();
                }
            } catch (ValidationException $exception) {
                throw $exception;
            } catch (\Throwable) {
            }
        }

        throw ValidationException::withMessages([$attribute => 'La fecha no es válida.']);
    }
}
