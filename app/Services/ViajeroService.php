<?php

namespace App\Services;

use App\Models\Pasaje;
use App\Models\Reserva;
use App\Models\User;
use App\Models\Viajero;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ViajeroService
{
    public static function agregarViajero(User $cliente, array $datos): Viajero
    {
        $datos = Validator::make($datos, [
            'nombre' => ['required', 'string', 'max:255'],
            'apellido' => ['required', 'string', 'max:255'],
            'tipo_documento' => ['required_with:documento_identidad', 'nullable', 'integer', 'in:1,2,3,4'],
            'documento_identidad' => ['nullable', 'string', 'max:255'],
            'fecha_nacimiento' => ['required', 'date_format:Y-m-d', 'before_or_equal:today'],
            'tipo_pasajero' => ['required', 'in:adulto,nino,infante'],
        ])->validate();

        $datos['usuario_id'] = $cliente->id;
        $datos['estatus'] = Viajero::ESTADO_ACTIVE;

        return Viajero::create($datos);
    }

    public static function eliminarViajero(User $cliente, int $viajeroId): void
    {
        DB::transaction(function () use ($cliente, $viajeroId) {
            $viajero = Viajero::query()
                ->whereKey($viajeroId)
                ->where('usuario_id', $cliente->id)
                ->lockForUpdate()
                ->firstOrFail();

            $pasajes = Pasaje::query()
                ->where(function (Builder $query) use ($cliente, $viajero) {
                    $query->where('viajero_id', $viajero->id);

                    if ($viajero->documento_identidad_hash !== null) {
                        $query->orWhere(function (Builder $query) use ($cliente, $viajero) {
                            $query->whereNull('viajero_id')
                                ->where('viajero_documento_hash', $viajero->documento_identidad_hash)
                                ->whereHas('reserva', function (Builder $query) use ($cliente) {
                                    $query->where('usuario_id', $cliente->id);
                                });
                        });
                    }
                });

            $reservaAbierta = (clone $pasajes)
                ->whereHas('reserva', function ($query) {
                    $query->where('estado_pago', Reserva::ESTADO_PAGO_NUEVO)
                        ->where('fecha_expiracion', '>', now())
                        ->whereDoesntHave('pago');
                })
                ->exists();

            Viajero::exigir(
                ! $reservaAbierta,
                'viajero',
                'Este viajero está incluido en una reserva abierta. Retíralo de la reserva antes de eliminarlo.',
            );

            if ($pasajes->exists()) {
                $viajero->update(['estatus' => Viajero::ESTADO_DELETE]);

                return;
            }

            $viajero->delete();
        }, 3);
    }
}
