<?php

namespace App\Services\Empresa;

use App\Models\Pasaje;
use App\Models\Programacion;
use App\Models\ProgramacionTramoPrecio;
use App\Models\Reserva;
use App\Models\Terminal;
use App\Models\TipoCambio;
use App\Models\UsuarioEmpresa;
use App\Models\Viajero;
use App\Services\ReservaService;
use App\Services\TasasServicioService;
use Closure;
use App\Support\PersonalData;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ReservaTaquillaService
{


    // Ningún borrador se persiste antes de esta operación completa.
    public static function registrar(UsuarioEmpresa $vendedor, int $tarifaId, array $comprador, array $pasajeros, array $pagos, ?string $codigo = null): Reserva
    {
        self::autorizar($vendedor);
        Reserva::exigir(count($pasajeros) > 0, 'pasajeros', 'Agrega al menos un pasajero.');

        return DB::transaction(function () use ($vendedor, $tarifaId, $comprador, $pasajeros, $pagos, $codigo) {
            $tarifa = ProgramacionTramoPrecio::findOrFail($tarifaId);
            Programacion::bloquear($tarifa->programacion_id);
            if ($codigo !== null) {
                $existente = Reserva::taquillaEmpresa($vendedor->empresa_id)
                    ->where('usuario_empresa_id', $vendedor->id)->where('codigo_referencia', $codigo)->first();
                if ($existente !== null) {
                    return $existente->detalle();
                }
            }
            $reserva = self::crear($vendedor, $tarifaId, $codigo);
            self::guardarComprador($vendedor, $reserva->id, $comprador);
            foreach ($pasajeros as $datos) {
                self::agregarPasajero($vendedor, $reserva->id, $datos);
            }

            return PagoTaquillaService::registrarPagos($vendedor, $reserva->id, $pagos);
        }, 3);
    }

    public static function crear(UsuarioEmpresa $vendedor, int $tarifaId, ?string $codigo = null): Reserva
    {
        self::autorizar($vendedor);

        return DB::transaction(function () use ($vendedor, $tarifaId, $codigo) {
            $tarifa = ProgramacionTramoPrecio::findOrFail($tarifaId);
            $programacion = Programacion::paraTaquilla($vendedor->empresa_id, soloFuturas: false)->findOrFail($tarifa->programacion_id);
            self::validarSalida($programacion, $vendedor, $tarifa);
            $disponibilidad = Pasaje::disponibilidad($programacion, $tarifa, Terminal::obtenerSecuenciaRuta($programacion));
            Reserva::exigir($disponibilidad['cupo_tramo'] > 0, 'tarifaId', 'No quedan puestos disponibles para este tramo.');
            $cambio = TipoCambio::vigente();
            Reserva::exigir($cambio !== null, 'tarifaId', 'No hay una tasa de cambio disponible.');
            
            return Reserva::create([
                'usuario_id' => null,
                'usuario_empresa_id' => $vendedor->id,
                'origen_venta' => Reserva::ORIGEN_TAQUILLA,
                'receptor_pago' => 'empresa',
                'programacion_id' => $programacion->id,
                'programacion_tramo_precio_id' => $tarifa->id,
                'origen_terminal_id' => $tarifa->origen_terminal_id,
                'destino_terminal_id' => $tarifa->destino_terminal_id,
                'tipos_cambios_id' => $cambio->id,
                'codigo_referencia' => $codigo ?? 'TQ-'.Reserva::generarLocalizador(7),
                'monto_pasajes' => $tarifa->precio,
                'descuento_aplicado' => '0.00',
                'tasa_servicio' => '0.00',
                'monto_total' => $tarifa->precio,
                'estado_pago' => Reserva::ESTADO_PAGO_NUEVO,
                'fecha_compra' => now(),
                'fecha_expiracion' => now()->addMinutes(ReservaService::MINUTOS_BLOQUEO),
            ])->detalle();
        });
    }

    public static function guardarComprador(UsuarioEmpresa $vendedor, int $reservaId, array $datos): Reserva
    {
        $datos = Validator::make($datos, [
            'nombre' => 'required|string|max:255',
            'telefono' => 'required|string|max:30',
            'email' => 'nullable|email|max:255',
        ], self::mensajes(), [
            'nombre' => 'nombre del comprador',
            'telefono' => 'teléfono del comprador',
            'email' => 'correo electrónico',
        ])->validate();

        return self::modificar($vendedor, $reservaId, function (Reserva $reserva) use ($datos) {
            $reserva->update(['comprador_json' => $datos]);
        });
    }

    public static function validarPasajero(array $datos): array
    {
        return Validator::make($datos, [
            'con_asiento' => 'sometimes|boolean',
            'nombre' => 'required|string|max:255',
            'apellido' => 'required|string|max:255',
            'tipo_documento' => 'nullable|required_with:documento_identidad|integer|in:1,2,3,4',
            'documento_identidad' => 'nullable|string|max:255',
            'fecha_nacimiento' => 'required|date_format:Y-m-d|before_or_equal:today',
            'tipo_pasajero' => 'required|in:adulto,nino,infante',
        ], self::mensajes(), [
            'tipo_documento' => 'tipo de documento',
            'documento_identidad' => 'documento de identidad',
            'fecha_nacimiento' => 'fecha de nacimiento',
            'tipo_pasajero' => 'tipo de pasajero',
        ])->validate();
    }

    public static function agregarPasajero(UsuarioEmpresa $vendedor, int $reservaId, array $datos): Reserva
    {
        $datos = self::validarPasajero($datos);

        return self::modificar($vendedor, $reservaId, function (Reserva $reserva, Programacion $programacion) use ($datos) {
            $tarifa = $programacion->tramoPrecios()->findOrFail($reserva->programacion_tramo_precio_id);
            $disponibilidad = Pasaje::disponibilidad($programacion, $tarifa, Terminal::obtenerSecuenciaRuta($programacion));
            $ocupaAsiento = $datos['tipo_pasajero'] !== 'infante' || (bool) ($datos['con_asiento'] ?? false);
            $precio = $ocupaAsiento ? $tarifa->precio : '0.00';
            Pasaje::exigir(! $ocupaAsiento || ($disponibilidad['cupo_tramo'] > 0 && $disponibilidad['asientos'] !== []), 'pasajero', 'No quedan puestos disponibles para este tramo.');
            // Modelo sin guardar: reutiliza el formato histórico sin crear un viajero del vendedor.
            $hash = PersonalData::hashDocumento($datos['documento_identidad'] ?? null);
            Pasaje::exigir($hash === null || ! $reserva->pasajes()->where('viajero_documento_hash', $hash)->exists(), 'documento_identidad', 'Este documento ya está incluido en la reserva.');
            $viajero = new Viajero($datos);
            $reserva->pasajes()->create([
                'viajero' => $viajero->datosParaPasaje(),
                'numero_asiento' => $ocupaAsiento ? $disponibilidad['asientos'][0] : null,
                'precio_base' => $precio,
                'descuento' => '0.00',
                'subtotal' => $precio,
                'tasa_servicio' => '0.00',
                'total' => $precio,
                'localizador' => Pasaje::generarLocalizador(7),
            ]);
            TasasServicioService::calcularTasasReserva($reserva);
        });
    }

    public static function removerPasajero(UsuarioEmpresa $vendedor, int $reservaId, int $pasajeId): Reserva
    {
        return self::modificar($vendedor, $reservaId, function (Reserva $reserva, Programacion $programacion) use ($pasajeId) {
            $reserva->pasajes()->findOrFail($pasajeId)->delete();
            if ($reserva->pasajes()->exists()) {
                TasasServicioService::calcularTasasReserva($reserva);
            } else {
                $tarifa = $programacion->tramoPrecios()->findOrFail($reserva->programacion_tramo_precio_id);
                $reserva->update([
                    'monto_pasajes' => $tarifa->precio,
                    'descuento_aplicado' => '0.00',
                    'tasa_servicio' => '0.00',
                    'monto_total' => $tarifa->precio,
                ]);
            }
        });
    }

    public static function cancelar(UsuarioEmpresa $vendedor, int $reservaId): Reserva
    {
        self::autorizar($vendedor);

        return DB::transaction(function () use ($vendedor, $reservaId) {
            $reserva = Reserva::taquillaEmpresa($vendedor->empresa_id)->lockForUpdate()->findOrFail($reservaId);
            Reserva::exigir($reserva->estado_pago === Reserva::ESTADO_PAGO_NUEVO, 'reserva', 'Solo se puede cancelar una reserva nueva desde taquilla.');
            $reserva->update(['estado_pago' => Reserva::ESTADO_PAGO_CANCELADO, 'fecha_expiracion' => null]);

            return $reserva->detalle();
        });
    }

    public static function autorizar(UsuarioEmpresa $vendedor): void
    {
        abort_unless($vendedor->hasPermission('reservas', 'add'), 403);
        Reserva::exigir($vendedor->empresa?->estatus === 1, 'empresa', 'La empresa no está activa para vender.');
    }

    public static function validarSalida(Programacion $programacion, UsuarioEmpresa $vendedor, ProgramacionTramoPrecio $tarifa): void
    {
        Reserva::exigir(
            (int) $programacion->viaje->empresa_id === (int) $vendedor->empresa_id
                && (int) $programacion->estatus === Programacion::ESTADO_PROGRAMADO
                && $programacion->transporte->tipo_transporte === $vendedor->empresa->getTipoTransporte(),
            'programacion',
            'La salida ya no está disponible para vender.',
        );
        $tarifa->validarSalida(validarHora: false);
    }

    private static function modificar(UsuarioEmpresa $vendedor, int $reservaId, Closure $accion): Reserva
    {
        self::autorizar($vendedor);

        return DB::transaction(function () use ($vendedor, $reservaId, $accion) {
            $reserva = Reserva::taquillaEmpresa($vendedor->empresa_id)->lockForUpdate()->findOrFail($reservaId);
            $reserva->validarEditable();
            $programacion = Programacion::bloquear($reserva->programacion_id);
            self::validarSalida($programacion, $vendedor, $reserva->tramoPrecio);
            $accion($reserva, $programacion);

            return $reserva->refresh()->detalle();
        }, 3);
    }

    public static function mensajes(): array
    {
        return [
            'boolean' => 'Indica si el infante ocupa asiento.',
            'required' => 'El campo :attribute es obligatorio.',
            'required_with' => 'El campo :attribute es obligatorio cuando se indica un documento.',
            'string' => 'El campo :attribute debe ser un texto.',
            'max' => 'El campo :attribute no debe superar :max caracteres.',
            'email' => 'Ingresa un correo electrónico válido.',
            'integer' => 'El campo :attribute debe ser un número entero.',
            'in' => 'Selecciona una opción válida para :attribute.',
            'date_format' => 'La fecha debe tener el formato año-mes-día.',
            'before_or_equal' => 'La fecha no puede ser futura.',
        ];
    }
}
