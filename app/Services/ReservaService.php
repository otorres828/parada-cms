<?php

namespace App\Services;

use App\Models\Cupon;
use App\Models\DatoBancario;
use App\Models\PagoReserva;
use App\Models\Programacion;
use App\Models\ProgramacionTramoPrecio;
use App\Models\Reserva;
use App\Models\TasaServicio;
use App\Models\User;
use App\Models\Viajero;
use Carbon\Carbon;
use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ReservaService
{
    public const MINUTOS_BLOQUEO = 20;

    // 1. Al continuar desde el itinerario, crea solo la reserva NUEVA con la cotización de un pasaje.
    public static function aplicarReserva(User $cliente, int $tarifaId): Reserva
    {
        self::validarCliente($cliente);

        return DB::transaction(function () use ($cliente, $tarifaId) {

            $referencia = ProgramacionTramoPrecio::findOrFail($tarifaId);
            $programacion = self::bloquearProgramacion($referencia->programacion_id);
            $tarifa = ProgramacionTramoPrecio::where('programacion_id', $programacion->id)->lockForUpdate()->findOrFail($tarifaId);
            $terminales = self::terminales($programacion);
            self::validarSalida($programacion, $tarifa->origen_terminal_id, $terminales);
            self::intervalo($terminales, $tarifa->origen_terminal_id, $tarifa->destino_terminal_id);
            self::exigir(bccomp($tarifa->precio, '0', 2) >= 0, 'tarifa', 'La tarifa no puede ser negativa.');
            $disponibilidad = self::disponibilidad($programacion, $tarifa, $terminales);
            self::exigir($disponibilidad['cupo_tramo'] > 0, 'tarifa', 'No hay cupo disponible para este trayecto.');
            $tasa = TasaServicio::paraPrecio($tarifa->precio)->calcular($tarifa->precio);

            $reserva = Reserva::create([
                'usuario_id' => $cliente->id,
                'programacion_id' => $programacion->id,
                'origen_terminal_id' => $tarifa->origen_terminal_id,
                'destino_terminal_id' => $tarifa->destino_terminal_id,
                'programacion_tramo_precio_id' => $tarifa->id,
                'codigo_referencia' => (string) Str::ulid(),
                'monto_pasajes' => $tarifa->precio,
                'descuento_aplicado' => '0.00',
                'tasa_servicio' => $tasa,
                'monto_total' => bcadd($tarifa->precio, $tasa, 2),
                'estado_pago' => Reserva::ESTADO_PAGO_NUEVO,
                'fecha_compra' => now(),
                'fecha_expiracion' => now()->addMinutes(self::MINUTOS_BLOQUEO),
            ]);

            return self::detalle($reserva);
        }, 3);
    }

    // 2. Sincroniza la lista completa de pasajeros y sus asientos; agrega o retira pasajes de la misma reserva.
    public static function registrarPasajeros(User $cliente, int $reservaId, array $pasajeros): Reserva
    {
        $datos = Validator::make(['pasajeros' => $pasajeros], [
            'pasajeros' => 'present|array|max:200',
            'pasajeros.*.numero_asiento' => 'required|integer|min:1|distinct',
            'pasajeros.*.nombre' => 'required|string|max:255',
            'pasajeros.*.apellido' => 'required|string|max:255',
            'pasajeros.*.tipo_documento' => 'required_with:pasajeros.*.documento_identidad|nullable|integer|in:1,2,3,4',
            'pasajeros.*.documento_identidad' => 'nullable|string|max:255',
            'pasajeros.*.fecha_nacimiento' => 'required|date_format:Y-m-d|before_or_equal:today',
            'pasajeros.*.tipo_pasajero' => 'required|in:adulto,nino,infante',
        ])->validate()['pasajeros'];

        return self::conReserva($cliente, $reservaId, function ($reserva) use ($cliente, $datos) {
            self::validarEditable($reserva);
            $programacion = self::bloquearProgramacion($reserva->programacion_id);
            $tarifa = ProgramacionTramoPrecio::where('programacion_id', $programacion->id)
                ->where('origen_terminal_id', $reserva->origen_terminal_id)
                ->where('destino_terminal_id', $reserva->destino_terminal_id)
                ->lockForUpdate()->findOrFail($reserva->programacion_tramo_precio_id);
            $terminales = self::terminales($programacion);
            self::validarSalida($programacion, $tarifa->origen_terminal_id, $terminales);
            self::exigir(bccomp($tarifa->precio, '0', 2) >= 0, 'tarifa', 'La tarifa no puede ser negativa.');
            $pasajes = $reserva->pasajes()->with('viajero')->orderBy('numero_asiento')->get();
            $asignaciones = collect($datos)->keyBy(fn ($p) => (int) $p['numero_asiento']);
            self::exigir($asignaciones->count() === count($datos), 'pasajeros', 'No repitas un asiento.');
            $asientos = $asignaciones->keys()->all();
            $disponibilidad = self::disponibilidad($programacion, $tarifa, $terminales, $reserva->id);
            self::exigir(! array_diff($asientos, $disponibilidad['asientos']), 'asientos', 'Uno de los asientos ya no está disponible para este trayecto.');
            self::exigir(count($asientos) <= $disponibilidad['cupo_tramo'], 'asientos', 'La selección supera el cupo de venta de este trayecto.');
            $anteriores = $pasajes->keyBy('numero_asiento');
            $cambiaLista = count($asientos) !== $pasajes->count() || array_diff($asientos, $anteriores->keys()->all());
            $codigoCupon = $reserva->cupon_id ? Cupon::findOrFail($reserva->cupon_id)->codigo : null;
            $reserva->pasajes()->whereNotIn('numero_asiento', $asientos)->delete();

            foreach ($asignaciones as $asiento => $persona) {
                $pasaje = $anteriores->get($asiento);
                if (! $pasaje) {
                    $pasaje = $reserva->pasajes()->create([
                        'numero_asiento' => $asiento,
                        'precio_base' => $tarifa->precio,
                        'descuento' => '0.00',
                        'subtotal' => $tarifa->precio,
                        'tasa_servicio' => '0.00',
                        'total' => $tarifa->precio,
                        'localizador' => (string) Str::random(20),
                    ]);
                }
                unset($persona['numero_asiento']);
                $persona = array_intersect_key($persona, array_flip(['nombre', 'apellido', 'tipo_documento', 'documento_identidad', 'fecha_nacimiento', 'tipo_pasajero']));
                $persona['usuario_id'] = $reserva->usuario_id;
                $actual = $pasaje->viajero;
                if ($actual && ! $actual->fill($persona)->isDirty()) {
                    continue;
                }
                // No modifica datos de viajeros que pudieran estar asociados a otros boletos.
                $viajero = Viajero::create($persona);
                $pasaje->update(['viajero_id' => $viajero->id]);
            }

            if ($asignaciones->isEmpty()) {
                if ($reserva->cupon_id) {
                    app(CuponService::class)->cancelarYLiberarCupon($reserva);
                    $reserva->refresh();
                }
                $tasa = TasaServicio::paraPrecio($tarifa->precio)->calcular($tarifa->precio);
                $reserva->update([
                    'cupon_id' => null,
                    'monto_pasajes' => $tarifa->precio,
                    'descuento_aplicado' => '0.00',
                    'tasa_servicio' => $tasa,
                    'monto_total' => bcadd($tarifa->precio, $tasa, 2),
                ]);

                return self::detalle($reserva);
            }

            if ($cambiaLista && $codigoCupon !== null) {
                // Reparte nuevamente el descuento entre los pasajeros actuales dentro de la misma transacción.
                app(CuponService::class)->cancelarYLiberarCupon($reserva);
                self::aplicarCupon($cliente, $reserva->id, $codigoCupon);
            }

            return self::detalle(TasasServicioService::calcularTasasReserva($reserva->id));
        });
    }

    // 3. Retiene el cupón y aplica el descuento a la reserva vigente.
    public static function aplicarCupon(User $cliente, int $reservaId, ?string $codigo): Reserva
    {
        return self::conReserva($cliente, $reservaId, function ($reserva) use ($codigo) {
            self::validarEditable($reserva);
            self::validarPasajeros($reserva);

            if ($codigo === null || trim($codigo) === '') {
                return app(CuponService::class)->removerCupon($reserva);
            }

            return self::detalle(app(CuponService::class)->aplicarCupon($reserva, $codigo));
        });
    }

    // 4. Resumen antes de pagar. Repetirlo conserva la tasa histórica ya calculada.
    public static function prepararResumen(User $cliente, int $reservaId): Reserva
    {
        return self::conReserva($cliente, $reservaId, function ($reserva) {
            self::validarEditable($reserva);
            self::validarPasajeros($reserva);
            self::validarCuponReserva($reserva);

            return self::detalle(TasasServicioService::calcularTasasReserva($reserva->id));
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

            self::exigir($datoBancario !== null, 'metodoPagoId', 'El método de pago no está activo.');

            if ($reserva->estado_pago === Reserva::ESTADO_PAGO_PENDIENTE) {
                $pagoReserva = $reserva->pago()->first();
                self::exigir($pagoReserva?->metodo_pago === $metodoPagoId, 'metodoPagoId', 'La reserva ya tiene otro método de pago pendiente.');

                return self::detalle($reserva);
            }
            self::validarEditable($reserva);
            self::validarPasajeros($reserva);
            self::validarCuponReserva($reserva);
            $reserva = TasasServicioService::calcularTasasReserva($reserva->id);
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
            self::exigir(bccomp($reserva->monto_total, $montoConfirmado, 2) === 0, 'pago', 'El cobro no coincide con el total de la reserva.');
            if ($reserva->estado_pago === Reserva::ESTADO_PAGO_PAGADO) {
                return self::detalle($reserva);
            }
            self::validarVigente($reserva);
            self::exigir($reserva->estado_pago === Reserva::ESTADO_PAGO_PENDIENTE, 'reserva', 'La reserva no tiene un pago pendiente.');
            self::validarPasajeros($reserva);
            self::validarCuponReserva($reserva);
            $reserva->update(['estado_pago' => Reserva::ESTADO_PAGO_PAGADO]);

            return self::detalle($reserva);
        });
    }

    public static function cancelarReserva(User $cliente, int $reservaId): Reserva
    {
        return self::conReserva($cliente, $reservaId, function ($reserva) {
            self::exigir(in_array($reserva->estado_pago, [Reserva::ESTADO_PAGO_NUEVO, Reserva::ESTADO_PAGO_CANCELADO], true), 'reserva', 'Un pago pendiente debe resolverse antes de cancelar la reserva.');
            $reserva->update(['estado_pago' => Reserva::ESTADO_PAGO_CANCELADO]);
            app(CuponService::class)->cancelarYLiberarCupon($reserva);

            return self::detalle($reserva->refresh());
        });
    }

    // Solo tras recibir un rechazo definitivo del proveedor; un timeout no confirma un fallo.
    public static function marcarPagoFallido(int $reservaId): Reserva
    {
        return self::conReserva(null, $reservaId, function ($reserva) {
            self::exigir(in_array($reserva->estado_pago, [Reserva::ESTADO_PAGO_PENDIENTE, Reserva::ESTADO_PAGO_FALLIDO], true), 'reserva', 'No se puede marcar este pago como fallido.');
            $reserva->update(['estado_pago' => Reserva::ESTADO_PAGO_FALLIDO]);

            return self::detalle($reserva);
        });
    }

    // La consulta es orientativa; registrarPasajeros verifica los asientos bajo bloqueo transaccional.
    public static function consultarDisponibilidad(int $tarifaId): array
    {
        $tarifa = ProgramacionTramoPrecio::findOrFail($tarifaId);
        $programacion = Programacion::with(['viaje.tramos', 'viaje.empresa', 'autobus'])->findOrFail($tarifa->programacion_id);
        $terminales = self::terminales($programacion);
        self::validarSalida($programacion, $tarifa->origen_terminal_id, $terminales);

        return self::disponibilidad($programacion, $tarifa, $terminales);
    }

    // Consulta administrativa por origen/destino, también para salidas históricas o inactivas.
    // Carga las reservas una sola vez para todas las programaciones del listado.
    public static function consultarDisponibilidadPorTramos(Collection $programaciones): array
    {
        if ($programaciones->isEmpty()) {
            return [];
        }

        $programaciones->loadMissing(['viaje.tramos', 'autobus', 'tramoPrecios.origenTerminal', 'tramoPrecios.destinoTerminal']);
        $reservas = self::reservasQueBloquean()->whereIn('programacion_id', $programaciones->modelKeys())
            ->with('pasajes')->get()->groupBy('programacion_id');
        $resultado = [];

        foreach ($programaciones as $programacion) {
            $terminales = self::terminales($programacion);
            $resultado[$programacion->id] = [];

            foreach ($programacion->tramoPrecios as $tarifa) {
                $resultado[$programacion->id][$tarifa->id] = self::disponibilidad(
                    $programacion, $tarifa, $terminales, null, $reservas->get($programacion->id, new Collection)
                ) + [
                    'origen' => $tarifa->origenTerminal?->nombre ?? '—',
                    'destino' => $tarifa->destinoTerminal?->nombre ?? '—',
                ];
            }
        }

        return $resultado;
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
            $referencia = $consulta->firstOrFail();
            self::bloquearProgramacion($referencia->programacion_id);
            $reserva = $consulta->lockForUpdate()->firstOrFail();

            return $accion($reserva);
        }, 3);
    }

    private static function bloquearProgramacion(int $id): Programacion
    {
        return Programacion::with(['viaje.tramos', 'viaje.empresa', 'autobus'])->whereKey($id)->lockForUpdate()->firstOrFail();
    }

    private static function validarCliente(User $cliente): void
    {
        self::exigir($cliente->exists && User::whereKey($cliente->id)->where('status', User::ESTADO_ACTIVE)->exists(), 'cliente', 'Debes iniciar sesión con una cuenta activa.');
    }

    private static function validarEditable(Reserva $reserva): void
    {
        self::validarVigente($reserva);
        self::exigir($reserva->estado_pago === Reserva::ESTADO_PAGO_NUEVO && ! $reserva->pago()->exists(), 'reserva', 'Solo se puede editar una reserva nueva y sin cobros registrados.');
    }

    private static function validarVigente(Reserva $reserva): void
    {
        if ($reserva->estado_pago !== Reserva::ESTADO_PAGO_NUEVO) {
            return;
        }

        self::exigir($reserva->fecha_expiracion !== null && $reserva->fecha_expiracion->isFuture(), 'reserva', 'La reserva venció. Selecciona nuevamente los asientos.');
    }

    private static function validarPasajeros(Reserva $reserva): void
    {
        self::exigir($reserva->pasajes()->exists() && ! $reserva->pasajes()->whereNull('viajero_id')->exists(), 'pasajeros', 'Completa los pasajeros antes de continuar al pago.');
    }

    private static function validarCuponReserva(Reserva $reserva): void
    {
        app(CuponService::class)->validarCuponAplicado($reserva);
    }

    private static function reservasQueBloquean(): Builder
    {
        return Reserva::where(function ($query) {
            $query->whereIn('estado_pago', [Reserva::ESTADO_PAGO_PAGADO, Reserva::ESTADO_PAGO_PENDIENTE])
                ->orWhere(function ($query) {
                    $query->where('estado_pago', Reserva::ESTADO_PAGO_NUEVO)
                        ->where('fecha_expiracion', '>', now());
                });
        });
    }

    private static function terminales(Programacion $programacion): array
    {
        $viaje = $programacion->viaje;
        self::exigir($viaje !== null, 'viaje', 'La ruta no existe.');
        $terminales = [(int) $viaje->origen_terminal_id];
        $orden = 0;
        foreach ($viaje->tramos as $tramo) {
            self::exigir((int) $tramo->origen_terminal_id === end($terminales) && $tramo->orden > $orden, 'viaje', 'La secuencia de tramos de la ruta es inválida.');
            $terminales[] = (int) $tramo->destino_terminal_id;
            $orden = $tramo->orden;
        }
        if ($viaje->tramos->isEmpty()) {
            $terminales[] = (int) $viaje->destino_terminal_id;
        }
        self::exigir(end($terminales) === (int) $viaje->destino_terminal_id && count($terminales) === count(array_unique($terminales)), 'viaje', 'La ruta debe tener terminales distintos y un destino final coherente.');

        return $terminales;
    }

    private static function intervalo(array $terminales, int $origen, int $destino): array
    {
        $inicio = array_search($origen, $terminales, true);
        $fin = array_search($destino, $terminales, true);
        self::exigir($inicio !== false && $fin !== false && $inicio < $fin, 'trayecto', 'El origen y destino no forman un trayecto válido de esta ruta.');

        return [$inicio, $fin];
    }

    private static function validarSalida(Programacion $programacion, int $origen, array $terminales): void
    {
        self::exigir($programacion->estatus === 1 && $programacion->viaje->estatus && $programacion->viaje->empresa?->estatus && $programacion->autobus?->estatus && ! $programacion->autobus->es_plantilla && (int) $programacion->autobus->empresa_id === (int) $programacion->viaje->empresa_id, 'programacion', 'La salida no está habilitada para venta.');
        $posicion = array_search($origen, $terminales, true);
        self::exigir($posicion !== false, 'trayecto', 'El origen no pertenece a la ruta.');
        $salida = Carbon::parse($programacion->fecha_salida->format('Y-m-d').' '.$programacion->hora_salida);
        foreach ($programacion->viaje->tramos->take($posicion) as $tramo) {
            self::exigir($tramo->duracion_estimada !== null, 'programacion', 'Falta la duración para calcular el embarque intermedio.');
            [$h, $m, $s] = array_map('intval', explode(':', $tramo->duracion_estimada));
            $salida->addSeconds($h * 3600 + $m * 60 + $s);
        }
        self::exigir($salida->isFuture(), 'programacion', 'La hora estimada de salida desde este terminal ya pasó.');
    }

    private static function disponibilidad(Programacion $programacion, ProgramacionTramoPrecio $tarifa, array $terminales, ?int $excluirReservaId = null, ?Collection $reservas = null): array
    {
        [$inicio, $fin] = self::intervalo($terminales, (int) $tarifa->origen_terminal_id, (int) $tarifa->destino_terminal_id);
        $reservas ??= self::reservasQueBloquean()->where('programacion_id', $programacion->id)
            ->when($excluirReservaId !== null, fn ($q) => $q->whereKeyNot($excluirReservaId))
            ->with(['pasajes' => fn ($q) => $q->lockForUpdate()])->lockForUpdate()->get();
        $ocupados = [];
        foreach ($reservas as $reserva) {
            if ($reserva->origen_terminal_id === null || $reserva->destino_terminal_id === null) {
                // Los boletos históricos sin trayecto bloquean todo el recorrido por precaución.
                $solapa = true;
            } else {
                [$a, $b] = self::intervalo($terminales, (int) $reserva->origen_terminal_id, (int) $reserva->destino_terminal_id);
                $solapa = $inicio < $b && $a < $fin;
            }
            if ($solapa) {
                $ocupados = array_merge($ocupados, $reserva->pasajes->pluck('numero_asiento')->filter()->all());
            }
        }
        $capacidad = max(0, min((int) $programacion->asientos_totales, (int) $programacion->autobus?->total_asientos));
        $libres = $capacidad > 0 ? array_values(array_diff(range(1, $capacidad), $ocupados)) : [];
        $cantidadOcupados = $capacidad - count($libres);
        $limite = $tarifa->asientos_maximos_permitidos === null ? $capacidad : min($capacidad, max(0, $tarifa->asientos_maximos_permitidos));
        $cupo = max(0, $limite - $cantidadOcupados);

        return [
            'asientos' => $cupo > 0 ? $libres : [],
            'cupo_tramo' => $cupo,
            'capacidad' => $limite,
            'ocupados' => $cantidadOcupados,
            'disponibles' => $cupo,
        ];
    }

    private static function detalle(Reserva $reserva): Reserva
    {
        return $reserva->load(['pasajes' => fn ($q) => $q->lockForUpdate(), 'pasajes.viajero', 'origenTerminal', 'destinoTerminal']);
    }

    private static function exigir(bool $condicion, string $campo, string $mensaje): void
    {
        if (! $condicion) {
            throw ValidationException::withMessages([$campo => $mensaje]);
        }
    }
}
