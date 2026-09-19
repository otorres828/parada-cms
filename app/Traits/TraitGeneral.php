<?php

namespace App\Traits;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

trait TraitGeneral {

    public static function date(string $value): string
    {
        foreach (['Y-m-d', 'd-m-Y'] as $format) {
            try {
                $date = Carbon::createFromFormat('!' . $format, $value);
                if ($date && $date->format($format) === $value) {
                    return $date->toDateString();
                }
            } catch (\Throwable) {
            }
        }
        throw ValidationException::withMessages(['date_from' => 'La fecha no es válida.']);
    }
    
}
