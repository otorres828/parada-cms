<?php

namespace App\Services;

use App\Models\Pasaje;
use App\Models\Programacion;
use App\Models\ProgramacionTramoPrecio;
use App\Models\Reserva;
use App\Models\TasaServicio;
use App\Models\Terminal;
use App\Models\TipoCambio;
use App\Models\User;
use App\Models\Viajero;
use Closure;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ReservaService
{
    public const MINUTOS_BLOQUEO = 20;

    // 1. Crea o reinicia la reserva NUEVA del cliente para esta programación.
    public static function aplicarReserva(User $cliente, int $tarifaId, ?int $reprogramacionId = null): Reserva
    {
        return DB::transaction(function () use ($cliente, $tarifaId, $reprogramacionId) {

            $tarifa = ProgramacionTramoPrecio::findOrFail($tarifaId);
            $reserva = Reserva::where('usuario_id', $cliente->id)
                ->where('programacion_id', $tarifa->programacion_id)
                ->where('estado_pago', Reserva::ESTADO_PAGO_NUEVO)
                ->whereDoesntHave('pago')
                ->orderByDesc('id')
                ->first();

            $reservaOriginal = null;

            if ($reprogramacionId !== null) {
                $reservaOriginal = Reserva::query()
                    ->where('usuario_id', $cliente->id)
                    ->lockForUpdate()
                    ->findOrFail($reprogramacionId);

                Reserva::exigir(
                    $reservaOriginal->estado_pago === Reserva::ESTADO_PAGO_PAGADO,
                    'reprogramacion_id',
                    'Solo se pueden reprogramar reservas pagadas.',
                );

                Reserva::exigir(
                    ! Reserva::reservasQueBloqueanAsientos()
                        ->where('reprogramacion_id', $reservaOriginal->id)
                        ->when($reserva !== null, fn ($query) => $query->whereKeyNot($reserva->id))
                        ->exists(),
                    'reprogramacion_id',
                    'Esta reserva ya tiene una reprogramación activa.',
                );
            }

            $programacion = Programacion::query()
                ->with(['viaje.tramos', 'viaje.empresa', 'transporte'])
                ->findOrFail($tarifa->programacion_id);

            $terminales = Terminal::obtenerSecuenciaRuta($programacion);

            if ($reserva !== null) {
                if ($reserva->cupon_id !== null) {
                    app(CuponService::class)->cancelarYLiberarCupon($reserva);
                }
                $reserva->pasajes()->delete();
            }

            $disponibilidad = Pasaje::disponibilidad($programacion, $tarifa, $terminales);

            Pasaje::exigir($disponibilidad['cupo_tramo'] > 0, 'tarifa', 'No hay cupo disponible para este trayecto.');

            $exoneracionTasa = TasasServicioService::obtenerExoneracionTasa($programacion, $reservaOriginal);

            $tipoCambio = TipoCambio::vigente();
            Reserva::exigir(
                $tipoCambio !== null,
                'tipo_cambio',
                'No hay una tasa de cambio disponible. Intenta nuevamente más tarde.',
            );

            $tasa = $exoneracionTasa === null
                ? TasaServicio::paraPrecio($tarifa->precio)->calcular($tarifa->precio)
                : '0.00';

            $reserva ??= new Reserva;
            $prefijo = $programacion->transporte->tipo_transporte === \App\Models\Transporte::CARRO ? 'CA' : 'AU';
            if (! preg_match('/^'.$prefijo.'-[A-Z0-9]{10}$/', $reserva->codigo_referencia ?? '')) {
                do {
                    $codigo = $prefijo.'-'.strtoupper(Str::random(10));
                } while (Reserva::where('codigo_referencia', $codigo)->exists());
                $reserva->codigo_referencia = $codigo;
            }
            $reserva->fill([
                'usuario_id' => $cliente->id,
                'programacion_id' => $programacion->id,
                'origen_terminal_id' => $tarifa->origen_terminal_id,
                'destino_terminal_id' => $tarifa->destino_terminal_id,
                'programacion_tramo_precio_id' => $tarifa->id,
                'reprogramacion_id' => $reservaOriginal?->id,
                'tipos_cambios_id' => $tipoCambio->id,
                'codigo_referencia' => $reserva->codigo_referencia,
                'cupon_id' => null,
                'monto_pasajes' => $tarifa->precio,
                'descuento_aplicado' => '0.00',
                'exoneracion_tasa_json' => $exoneracionTasa,
                'tasa_servicio' => $tasa,
                'monto_total' => bcadd($tarifa->precio, $tasa, 2),
                'estado_pago' => Reserva::ESTADO_PAGO_NUEVO,
                'fecha_compra' => now(),
                'fecha_pago' => null,
                'fecha_expiracion' => now()->addMinutes(self::MINUTOS_BLOQUEO),
            ]);
            $reserva->save();

            return $reserva->detalle();

        }, 3);

    }

    // 2. Agrega un pasajero y recalcula el cupón y los importes de la reserva.
    public static function agregarPasajero(User $cliente, int $reservaId, array $pasajero): Reserva
    {
        $datos = Validator::make($pasajero, [
            'nombre' => ['required', 'string', 'max:255'],
            'apellido' => ['required', 'string', 'max:255'],
            'tipo_documento' => ['required_with:documento_identidad', 'nullable', 'integer', 'in:1,2,3,4'],
            'documento_identidad' => ['nullable', 'string', 'max:255'],
            'fecha_nacimiento' => ['required', 'date_format:Y-m-d', 'before_or_equal:today'],
            'tipo_pasajero' => ['required', 'in:adulto,nino,infante'],
        ])->validate();

        return self::conReserva($cliente, $reservaId, function ($reserva) use ($datos) {

            $reserva->validarEditable();

            $programacion = Programacion::bloquear($reserva->programacion_id);
            $reserva->validarVigente();

            $tarifa = ProgramacionTramoPrecio::query()
                ->where('programacion_id', $programacion->id)
                ->where('origen_terminal_id', $reserva->origen_terminal_id)
                ->where('destino_terminal_id', $reserva->destino_terminal_id)
                ->findOrFail($reserva->programacion_tramo_precio_id);

            $terminales = Terminal::obtenerSecuenciaRuta($programacion);

            $disponibilidad = Pasaje::disponibilidad(
                $programacion,
                $tarifa,
                $terminales,
            );

            Pasaje::exigir(
                $disponibilidad['cupo_tramo'] > 0 && $disponibilidad['asientos'] !== [],
                'pasajero',
                'No quedan puestos disponibles para este trayecto.',
            );

            $numeroAsiento = (int) $disponibilidad['asientos'][0];

            $datosViajero = $datos;
            $datosViajero['usuario_id'] = $reserva->usuario_id;

            $viajero = Viajero::create($datosViajero);

            $reserva->pasajes()->create([
                'viajero' => [
                    'version' => 1,
                    'nombre' => $viajero->nombre,
                    'apellido' => $viajero->apellido,
                    'tipo_documento' => $viajero->tipo_documento,
                    'documento_identidad' => $viajero->documento_identidad,
                    'fecha_nacimiento' => $viajero->fecha_nacimiento->toDateString(),
                    'tipo_pasajero' => $viajero->tipo_pasajero,
                ],
                'numero_asiento' => $numeroAsiento,
                'precio_base' => $tarifa->precio,
                'descuento' => '0.00',
                'subtotal' => $tarifa->precio,
                'tasa_servicio' => '0.00',
                'total' => $tarifa->precio,
                'localizador' => (string) Str::random(20),
            ]);

            return self::recalcularDespuesDeModificarPasajeros($reserva);
        });
    }

    // 3. Retira un pasaje de la reserva y recalcula el cupón y los importes restantes.
    public static function removerPasajero(User $cliente, int $reservaId, int $pasajeId): Reserva
    {
        return self::conReserva($cliente, $reservaId, function ($reserva) use ($pasajeId) {
            $reserva->validarEditable();
            Programacion::bloquear($reserva->programacion_id);

            $pasaje = $reserva->pasajes()->findOrFail($pasajeId);
            $pasaje->delete();

            if (! $reserva->pasajes()->exists()) {
                if ($reserva->cupon_id !== null) {
                    app(CuponService::class)->removerCupon($reserva, reservaBloqueada: true);
                    $reserva->refresh();
                }

                return self::restablecerCotizacionInicial($reserva);
            }

            return self::recalcularDespuesDeModificarPasajeros($reserva);
        });
    }

    // 4. Resumen antes de pagar. Repetirlo conserva la tasa histórica ya calculada.
    public static function prepararResumen(User $cliente, int $reservaId): Reserva
    {
        return self::conReserva($cliente, $reservaId, function ($reserva) {
            $reserva->validarEditable();
            app(CuponService::class)->validarCuponAplicado($reserva);

            return TasasServicioService::calcularTasasReserva($reserva)->detalle();
        });
    }

    public static function cancelarReserva(User $cliente, int $reservaId): Reserva
    {
        return self::conReserva($cliente, $reservaId, function ($reserva) {
            Reserva::exigir(in_array($reserva->estado_pago, [Reserva::ESTADO_PAGO_NUEVO, Reserva::ESTADO_PAGO_CANCELADO], true), 'reserva', 'Un pago pendiente debe resolverse antes de cancelar la reserva.');
            $reserva->update([
                'estado_pago' => Reserva::ESTADO_PAGO_CANCELADO,
                'fecha_expiracion' => null,
            ]);
            app(CuponService::class)->cancelarYLiberarCupon($reserva);

            return $reserva->refresh()->detalle();
        });
    }

    private static function conReserva(User $cliente, int $reservaId, Closure $accion): Reserva
    {
        return DB::transaction(function () use ($cliente, $reservaId, $accion) {
            $reserva = Reserva::query()
                ->whereKey($reservaId)
                ->where('usuario_id', $cliente->id)
                ->lockForUpdate()
                ->firstOrFail();

            return $accion($reserva);
        }, 3);
    }

    private static function recalcularDespuesDeModificarPasajeros(Reserva $reserva): Reserva
    {
        if ($reserva->cupon_id !== null) {
            $reserva = app(CuponService::class)->recalcularCupon($reserva);
        }

        return TasasServicioService::calcularTasasReserva($reserva)->detalle();
    }

    private static function restablecerCotizacionInicial(Reserva $reserva): Reserva
    {
        $tarifa = ProgramacionTramoPrecio::findOrFail($reserva->programacion_tramo_precio_id);
        $tasa = $reserva->exoneracion_tasa_json === null
            ? TasaServicio::paraPrecio($tarifa->precio)->calcular($tarifa->precio)
            : '0.00';

        $reserva->update([
            'cupon_id' => null,
            'monto_pasajes' => $tarifa->precio,
            'descuento_aplicado' => '0.00',
            'tasa_servicio' => $tasa,
            'monto_total' => bcadd($tarifa->precio, $tasa, 2),
        ]);

        return $reserva->detalle();
    }
}
