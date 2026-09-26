<?php

namespace App\Services;

use App\Models\DatoBancario;
use App\Models\ExoneracionTasaServicio;
use App\Models\PagoReserva;
use App\Models\Pasaje;
use App\Models\Programacion;
use App\Models\ProgramacionTramoPrecio;
use App\Models\Reserva;
use App\Models\TasaServicio;
use App\Models\Terminal;
use App\Models\User;
use App\Models\Viajero;
use Closure;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ReservaService
{
    public const MINUTOS_BLOQUEO = 20;

    // 1. Al continuar desde el itinerario, crea solo la reserva NUEVA con la cotización de un pasaje.
    public static function aplicarReserva(User $cliente, int $tarifaId, ?int $reprogramacionId = null): Reserva
    {
        self::validarCliente($cliente);

        return DB::transaction(function () use ($cliente, $tarifaId, $reprogramacionId) {

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
                    ! $reservaOriginal->reprogramado()->exists(),
                    'reprogramacion_id',
                    'Esta reserva ya fue reprogramada.',
                );
            }

            $referencia = ProgramacionTramoPrecio::findOrFail($tarifaId);
            $programacion = self::bloquearProgramacion($referencia->programacion_id);
            $tarifa = ProgramacionTramoPrecio::where('programacion_id', $programacion->id)->lockForUpdate()->findOrFail($tarifaId);
            $terminales = Terminal::obtenerSecuenciaRuta($programacion);
            Terminal::validarSalida($programacion, $tarifa->origen_terminal_id, $terminales);
            Terminal::obtenerIntervalo($terminales, $tarifa->origen_terminal_id, $tarifa->destino_terminal_id);
            ProgramacionTramoPrecio::exigir(bccomp($tarifa->precio, '0', 2) >= 0, 'tarifa', 'La tarifa no puede ser negativa.');
            $disponibilidad = Pasaje::disponibilidad($programacion, $tarifa, $terminales);
            Pasaje::exigir($disponibilidad['cupo_tramo'] > 0, 'tarifa', 'No hay cupo disponible para este trayecto.');
            $exoneracionTasa = self::obtenerExoneracionTasa($programacion, $reservaOriginal);
            $tasa = $exoneracionTasa === null
                ? TasaServicio::paraPrecio($tarifa->precio)->calcular($tarifa->precio)
                : '0.00';

            $reserva = Reserva::create([
                'usuario_id' => $cliente->id,
                'programacion_id' => $programacion->id,
                'origen_terminal_id' => $tarifa->origen_terminal_id,
                'destino_terminal_id' => $tarifa->destino_terminal_id,
                'programacion_tramo_precio_id' => $tarifa->id,
                'reprogramacion_id' => $reservaOriginal?->id,
                'codigo_referencia' => (string) Str::ulid(),
                'monto_pasajes' => $tarifa->precio,
                'descuento_aplicado' => '0.00',
                'exoneracion_tasa_json' => $exoneracionTasa,
                'tasa_servicio' => $tasa,
                'monto_total' => bcadd($tarifa->precio, $tasa, 2),
                'estado_pago' => Reserva::ESTADO_PAGO_NUEVO,
                'fecha_compra' => now(),
                'fecha_expiracion' => now()->addMinutes(self::MINUTOS_BLOQUEO),
            ]);

            if ($reservaOriginal) {
                $reservaOriginal->update([
                    'estado_pago' => Reserva::ESTADO_PAGO_REPROGRAMADO,
                    'fecha_expiracion' => null,
                ]);
            }

            return self::detalle($reserva);
        }, 3);
    }

    // 2. Agrega un pasajero y recalcula el cupón y los importes de la reserva.
    public static function agregarPasajero(User $cliente, int $reservaId, array $pasajero): Reserva
    {
        $datos = Validator::make($pasajero, [
            'numero_asiento' => ['required', 'integer', 'min:1'],
            'nombre' => ['required', 'string', 'max:255'],
            'apellido' => ['required', 'string', 'max:255'],
            'tipo_documento' => ['required_with:documento_identidad', 'nullable', 'integer', 'in:1,2,3,4'],
            'documento_identidad' => ['nullable', 'string', 'max:255'],
            'fecha_nacimiento' => ['required', 'date_format:Y-m-d', 'before_or_equal:today'],
            'tipo_pasajero' => ['required', 'in:adulto,nino,infante'],
        ])->validate();
        $datos['numero_asiento'] = (int) $datos['numero_asiento'];

        return self::conReserva($cliente, $reservaId, function ($reserva) use ($datos) {
            self::validarEditable($reserva);

            $programacion = self::bloquearProgramacion($reserva->programacion_id);
            $tarifa = ProgramacionTramoPrecio::query()
                ->where('programacion_id', $programacion->id)
                ->where('origen_terminal_id', $reserva->origen_terminal_id)
                ->where('destino_terminal_id', $reserva->destino_terminal_id)
                ->findOrFail($reserva->programacion_tramo_precio_id);
            $terminales = Terminal::obtenerSecuenciaRuta($programacion);
            Terminal::validarSalida($programacion, $tarifa->origen_terminal_id, $terminales);
            ProgramacionTramoPrecio::exigir(bccomp($tarifa->precio, '0', 2) >= 0, 'tarifa', 'La tarifa no puede ser negativa.');
            Pasaje::exigir(
                ! $reserva->pasajes()->where('numero_asiento', $datos['numero_asiento'])->exists(),
                'numero_asiento',
                'El asiento ya está registrado en esta reserva.',
            );

            $disponibilidad = Pasaje::disponibilidad(
                $programacion,
                $tarifa,
                $terminales,
                $reserva->id,
            );
            Pasaje::exigir(
                in_array($datos['numero_asiento'], $disponibilidad['asientos'], true),
                'numero_asiento',
                'El asiento ya no está disponible para este trayecto.',
            );
            Pasaje::exigir(
                $reserva->pasajes()->count() < $disponibilidad['cupo_tramo'],
                'numero_asiento',
                'La reserva alcanzó el cupo de venta permitido para este trayecto.',
            );

            $datosViajero = array_intersect_key($datos, array_flip([
                'nombre',
                'apellido',
                'tipo_documento',
                'documento_identidad',
                'fecha_nacimiento',
                'tipo_pasajero',
            ]));
            $datosViajero['usuario_id'] = $reserva->usuario_id;
            $viajero = Viajero::create($datosViajero);

            $reserva->pasajes()->create([
                'viajero_id' => $viajero->id,
                'numero_asiento' => $datos['numero_asiento'],
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
            self::validarEditable($reserva);
            self::bloquearProgramacion($reserva->programacion_id);

            $pasaje = $reserva->pasajes()->findOrFail($pasajeId);
            $pasaje->delete();

            if (! $reserva->pasajes()->exists()) {
                if ($reserva->cupon_id !== null) {
                    app(CuponService::class)->removerCupon($reserva);
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
            self::validarEditable($reserva);
            self::validarPasajeros($reserva);
            self::validarCuponReserva($reserva);

            return self::detalle(TasasServicioService::calcularTasasReserva($reserva));
        });
    }

    // 5. Al reportar el pago, congela el resumen y elimina la expiración durante la revisión.
    public static function pasarAPendiente(User $cliente, int $reservaId, int $metodoPagoId, string $referenciaPago, string $fechaPago, ?string $comprobante = null): Reserva
    {
        Validator::make(compact('metodoPagoId', 'referenciaPago', 'fechaPago'), [
            'metodoPagoId' => 'required|integer|exists:datos_bancarios,id',
            'referenciaPago' => 'required|string|max:255|unique:pagos_reservas,referencia_pago',
            'fechaPago' => 'required|date|before_or_equal:now',
        ], [], [
            'metodoPagoId' => 'Método de pago',
            'referenciaPago' => 'Referencia de pago',
            'fechaPago' => 'Fecha de pago',
        ])->validate();

        return self::conReserva($cliente, $reservaId, function ($reserva) use ($metodoPagoId, $referenciaPago, $fechaPago, $comprobante) {
            self::validarVigente($reserva);

            $datoBancario = DatoBancario::query()
                ->whereKey($metodoPagoId)
                ->where('estatus', DatoBancario::ACTIVO)
                ->first();

            DatoBancario::exigir($datoBancario !== null, 'metodoPagoId', 'El método de pago no está activo.');

            if ($reserva->estado_pago === Reserva::ESTADO_PAGO_PENDIENTE) {
                $pagoReserva = $reserva->pago()->first();
                PagoReserva::exigir($pagoReserva?->metodo_pago === $metodoPagoId, 'metodoPagoId', 'La reserva ya tiene otro método de pago pendiente.');

                return self::detalle($reserva);
            }
            self::validarEditable($reserva);
            self::validarPasajeros($reserva);
            self::validarCuponReserva($reserva);
            $reserva = TasasServicioService::calcularTasasReserva($reserva);
            $reserva->update([
                'estado_pago' => Reserva::ESTADO_PAGO_PENDIENTE,
                'fecha_expiracion' => null,
            ]);
            PagoReserva::create([
                'reserva_id' => $reserva->id,
                'total' => $reserva->monto_total,
                'tasa_servicio' => $reserva->tasa_servicio,
                'metodo_pago' => $metodoPagoId,
                'referencia_pago' => $referenciaPago,
                'fecha_pago' => $fechaPago,
                'comprobante' => $comprobante,
            ]);

            return self::detalle($reserva);
        });
    }

    // 6. Exclusivo del backend, después de verificar el cobro con la pasarela o el banco.
    // No exponer directamente como acción de Livewire o endpoint del comprador.
    public static function confirmarPago(int $reservaId, string $montoConfirmado, string $moneda = 'USD'): Reserva
    {
        Validator::make(['monto' => $montoConfirmado, 'moneda' => $moneda], [
            'monto' => 'required|decimal:0,2|min:0|max:9999999999.99',
            'moneda' => 'required|in:USD',
        ])->validate();

        return self::conReserva(null, $reservaId, function ($reserva) use ($montoConfirmado) {
            Reserva::exigir(bccomp($reserva->monto_total, $montoConfirmado, 2) === 0, 'pago', 'El cobro no coincide con el total de la reserva.');
            if ($reserva->estado_pago === Reserva::ESTADO_PAGO_PAGADO) {
                return self::detalle($reserva);
            }
            self::validarVigente($reserva);
            Reserva::exigir($reserva->estado_pago === Reserva::ESTADO_PAGO_PENDIENTE, 'reserva', 'La reserva no tiene un pago pendiente.');
            self::validarPasajeros($reserva);
            self::validarCuponReserva($reserva);
            $reserva->update([
                'estado_pago' => Reserva::ESTADO_PAGO_PAGADO,
                'fecha_pago' => now(),
            ]);

            return self::detalle($reserva);
        });
    }

    public static function cancelarReserva(User $cliente, int $reservaId): Reserva
    {
        return self::conReserva($cliente, $reservaId, function ($reserva) {
            Reserva::exigir(in_array($reserva->estado_pago, [Reserva::ESTADO_PAGO_NUEVO, Reserva::ESTADO_PAGO_CANCELADO], true), 'reserva', 'Un pago pendiente debe resolverse antes de cancelar la reserva.');
            $reserva->update(['estado_pago' => Reserva::ESTADO_PAGO_CANCELADO]);
            app(CuponService::class)->cancelarYLiberarCupon($reserva);

            return self::detalle($reserva->refresh());
        });
    }

    // Solo tras recibir un rechazo definitivo del proveedor; un timeout no confirma un fallo.
    public static function marcarPagoFallido(int $reservaId): Reserva
    {
        return self::conReserva(null, $reservaId, function ($reserva) {
            Reserva::exigir(in_array($reserva->estado_pago, [Reserva::ESTADO_PAGO_PENDIENTE, Reserva::ESTADO_PAGO_FALLIDO], true), 'reserva', 'No se puede marcar este pago como fallido.');
            $reserva->update(['estado_pago' => Reserva::ESTADO_PAGO_FALLIDO]);

            return self::detalle($reserva);
        });
    }

    private static function conReserva(?User $cliente, int $reservaId, Closure $accion): Reserva
    {
        if ($cliente) {
            self::validarCliente($cliente);
        }

        return DB::transaction(function () use ($cliente, $reservaId, $accion) {
            $consulta = Reserva::whereKey($reservaId);
            if ($cliente) {
                $consulta->where('usuario_id', $cliente->id);
            }
            $reserva = $consulta->lockForUpdate()->firstOrFail();

            return $accion($reserva);
        }, 3);
    }

    private static function bloquearProgramacion(int $id): Programacion
    {
        return Programacion::with(['viaje.tramos', 'viaje.empresa', 'autobus'])->whereKey($id)->lockForUpdate()->firstOrFail();
    }

    private static function obtenerExoneracionTasa(Programacion $programacion, ?Reserva $reservaOriginal): ?array
    {
        if ($reservaOriginal !== null) {
            return [
                'exoneracion_id' => null,
                'fecha_desde' => null,
                'fecha_hasta' => null,
                'motivo' => 'Reprogramación de la reserva '.$reservaOriginal->codigo_referencia,
            ];
        }

        $empresaId = (int) $programacion->viaje->empresa_id;
        $exoneracion = ExoneracionTasaServicio::vigenteParaEmpresa($empresaId);

        return $exoneracion?->snapshot();
    }

    private static function recalcularDespuesDeModificarPasajeros(Reserva $reserva): Reserva
    {
        if ($reserva->cupon_id !== null) {
            $reserva = app(CuponService::class)->recalcularCupon($reserva);
        }

        return self::detalle(TasasServicioService::calcularTasasReserva($reserva));
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

        return self::detalle($reserva);
    }

    private static function validarCliente(User $cliente): void
    {
        User::exigir($cliente->exists && User::whereKey($cliente->id)->where('status', User::ESTADO_ACTIVE)->exists(), 'cliente', 'Debes iniciar sesión con una cuenta activa.');
    }

    private static function validarEditable(Reserva $reserva): void
    {
        self::validarVigente($reserva);
        Reserva::exigir($reserva->estado_pago === Reserva::ESTADO_PAGO_NUEVO && ! $reserva->pago()->exists(), 'reserva', 'Solo se puede editar una reserva nueva y sin cobros registrados.');
    }

    private static function validarVigente(Reserva $reserva): void
    {
        if ($reserva->estado_pago !== Reserva::ESTADO_PAGO_NUEVO) {
            return;
        }

        Reserva::exigir($reserva->fecha_expiracion !== null && $reserva->fecha_expiracion->isFuture(), 'reserva', 'La reserva venció. Selecciona nuevamente los asientos.');
    }

    private static function validarPasajeros(Reserva $reserva): void
    {
        Pasaje::exigir($reserva->pasajes()->exists() && ! $reserva->pasajes()->whereNull('viajero_id')->exists(), 'pasajeros', 'Completa los pasajeros antes de continuar al pago.');
    }

    private static function validarCuponReserva(Reserva $reserva): void
    {
        app(CuponService::class)->validarCuponAplicado($reserva);
    }

    private static function detalle(Reserva $reserva): Reserva
    {
        return $reserva->load(['pasajes', 'pasajes.viajero', 'origenTerminal', 'destinoTerminal']);
    }

}
