<?php

namespace App\Services\Empresa;

use App\Models\DatoBancario;
use App\Models\PagoReserva;
use App\Models\Programacion;
use App\Models\Reserva;
use App\Models\UsuarioEmpresa;
use App\Services\PagoReservaService;
use App\Services\TasasServicioService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class PagoTaquillaService
{
    public static function validarPago(array $pago): array
    {
        return Validator::make($pago, [
            'tipo' => 'required|integer|in:2,3,4',
            'moneda' => 'required|in:USD,VES',
            'monto' => 'required|numeric|decimal:0,2|min:0.01|max:9999999999.99',
            'cuenta_id' => 'exclude_unless:tipo,3|required|integer',
            'referencia' => 'nullable|required_if:tipo,3,4|string|max:255',
        ], [
            'required' => 'El campo :attribute es obligatorio.',
            'required_if' => 'El campo :attribute es obligatorio para este método de pago.',
            'integer' => 'Selecciona una opción válida para :attribute.',
            'in' => 'Selecciona una opción válida para :attribute.',
            'numeric' => 'El monto debe ser numérico.',
            'decimal' => 'El monto debe tener como máximo dos decimales.',
            'min' => 'El monto debe ser mayor que cero.',
            'max' => 'El campo :attribute supera el máximo permitido.',
            'string' => 'La referencia debe ser texto.',
        ], [
            'cuenta_id' => 'cuenta receptora',
        ])->validate();
    }

    public static function registrarPagos(UsuarioEmpresa $vendedor, int $reservaId, array $pagos): Reserva
    {
        ReservaTaquillaService::autorizar($vendedor);
        abort_unless($vendedor->hasPermission('reservas', 'confirm'), 403);

        return DB::transaction(function () use ($vendedor, $reservaId, $pagos) {
            $reserva = Reserva::taquillaEmpresa($vendedor->empresa_id)->lockForUpdate()->findOrFail($reservaId);
            $reserva->validarEditable();
            ReservaTaquillaService::validarSalida(Programacion::bloquear($reserva->programacion_id), $vendedor, $reserva->tramoPrecio);
            Reserva::exigir(! empty($reserva->comprador_json['nombre']) && ! empty($reserva->comprador_json['telefono']), 'comprador', 'Completa los datos del comprador.');
            Reserva::exigir(count($pagos) > 0, 'pagos', 'Agrega los pagos recibidos.');
            $reserva = TasasServicioService::calcularTasasReserva($reserva);
            $suma = '0.00';
            foreach ($pagos as $pago) {
                $datos = self::validarPago($pago);
                $tipo = (int) $datos['tipo'];
                if ($tipo === PagoReserva::TIPO_PAGO_MOVIL) {
                    $cuenta = DatoBancario::where('empresa_id', $vendedor->empresa_id)
                        ->where('estatus', DatoBancario::ACTIVO)->findOrFail($datos['cuenta_id']);
                    Reserva::exigir($cuenta->tipo === DatoBancario::TIPO_PAGO_MOVIL, 'pagos', 'La cuenta no corresponde al método de pago.');
                }
                if ($tipo !== PagoReserva::TIPO_PAGO_EFECTIVO) {
                    Reserva::exigir(! PagoReserva::where('referencia_pago', $datos['referencia'])->exists(), 'pagos', 'La referencia de pago ya fue registrada.');
                }
                $monto = (string) $datos['monto'];
                if ($datos['moneda'] === 'VES') {
                    Reserva::exigir((float) $reserva->tipoCambio?->valor_usd > 0, 'pagos', 'No hay una tasa de cambio válida.');
                    $monto = number_format((float) $monto / (float) $reserva->tipoCambio->valor_usd, 2, '.', '');
                }
                $suma = bcadd($suma, $monto, 2);
                PagoReserva::create([
                    'reserva_id' => $reserva->id,
                    'tipo_pago' => $tipo,
                    'monto_recibido' => $monto,
                    'total' => $monto,
                    'tasa_servicio' => '0.00',
                    'metodo_pago' => $tipo === PagoReserva::TIPO_PAGO_MOVIL ? $datos['cuenta_id'] : null,
                    'referencia_pago' => $tipo === PagoReserva::TIPO_PAGO_EFECTIVO ? 'EF-'.Str::ulid() : $datos['referencia'],
                    'fecha_pago' => now(),
                ]);
            }
            Reserva::exigir(bccomp($suma, $reserva->monto_total, 2) === 0, 'pagos', 'La suma de los pagos debe coincidir con el total de la reserva.');
            $reserva->update(['estado_pago' => Reserva::ESTADO_PAGO_PENDIENTE, 'fecha_expiracion' => null]);

            return PagoReservaService::confirmarPago($reserva->id, $reserva->monto_total);
        });
    }

    public static function registrar(UsuarioEmpresa $vendedor, int $reservaId, int $tipo, ?int $cuentaId, ?string $referencia): Reserva
    {
        ReservaTaquillaService::autorizar($vendedor);
        Validator::make(compact('tipo', 'cuentaId', 'referencia'), [
            'tipo' => 'required|integer|in:'.PagoReserva::TIPO_PAGO_TRANSFERENCIA.','.PagoReserva::TIPO_PAGO_EFECTIVO,
            'cuentaId' => 'nullable|required_if:tipo,'.PagoReserva::TIPO_PAGO_TRANSFERENCIA.'|integer',
            'referencia' => 'nullable|required_if:tipo,'.PagoReserva::TIPO_PAGO_TRANSFERENCIA.'|string|max:255',
        ], [
            'required' => 'Selecciona la forma de pago.',
            'in' => 'La forma de pago no es válida.',
            'required_if' => 'El campo :attribute es obligatorio para transferencias.',
            'integer' => 'Selecciona una cuenta válida.',
            'string' => 'La referencia debe ser un texto.',
            'max' => 'La referencia no debe superar 255 caracteres.',
        ], ['cuentaId' => 'cuenta bancaria', 'referencia' => 'referencia de pago'])->validate();
        if ($tipo === PagoReserva::TIPO_PAGO_EFECTIVO) {
            abort_unless($vendedor->hasPermission('reservas', 'confirm'), 403);
        }

        return DB::transaction(function () use ($vendedor, $reservaId, $tipo, $cuentaId, $referencia) {
            $reserva = Reserva::taquillaEmpresa($vendedor->empresa_id)->lockForUpdate()->findOrFail($reservaId);
            $reserva->validarEditable();
            $programacion = Programacion::bloquear($reserva->programacion_id);
            ReservaTaquillaService::validarSalida($programacion, $vendedor, $reserva->tramoPrecio);
            Reserva::exigir(! empty($reserva->comprador_json['nombre']) && ! empty($reserva->comprador_json['telefono']), 'comprador', 'Guarda los datos del comprador antes de cobrar.');
            if ($tipo === PagoReserva::TIPO_PAGO_TRANSFERENCIA) {
                DatoBancario::where('empresa_id', $vendedor->empresa_id)->where('estatus', DatoBancario::ACTIVO)->findOrFail($cuentaId);
                Reserva::exigir(! PagoReserva::where('referencia_pago', $referencia)->exists(), 'referencia', 'Esta referencia ya fue registrada.');
            }
            $reserva = TasasServicioService::calcularTasasReserva($reserva);
            PagoReserva::create([
                'reserva_id' => $reserva->id,
                'total' => $reserva->monto_total,
                'tasa_servicio' => '0.00',
                'tipo_pago' => $tipo,
                'metodo_pago' => $tipo === PagoReserva::TIPO_PAGO_TRANSFERENCIA ? $cuentaId : null,
                'referencia_pago' => $tipo === PagoReserva::TIPO_PAGO_TRANSFERENCIA ? $referencia : 'EF-'.Str::ulid(),
                'fecha_pago' => now(),
            ]);
            $reserva->update(['estado_pago' => Reserva::ESTADO_PAGO_PENDIENTE, 'fecha_expiracion' => null]);

            return $tipo === PagoReserva::TIPO_PAGO_EFECTIVO
                ? PagoReservaService::confirmarPago($reserva->id, $reserva->monto_total)
                : $reserva->detalle();
        }, 3);
    }

    public static function confirmar(UsuarioEmpresa $vendedor, int $reservaId): Reserva
    {
        abort_unless($vendedor->hasPermission('reservas', 'confirm'), 403);

        return DB::transaction(function () use ($vendedor, $reservaId) {
            $reserva = Reserva::taquillaEmpresa($vendedor->empresa_id)->lockForUpdate()->findOrFail($reservaId);

            return PagoReservaService::confirmarPago($reserva->id, $reserva->monto_total);
        });
    }

    public static function rechazar(UsuarioEmpresa $vendedor, int $reservaId): Reserva
    {
        abort_unless($vendedor->hasPermission('reservas', 'confirm'), 403);

        return DB::transaction(function () use ($vendedor, $reservaId) {
            $reserva = Reserva::taquillaEmpresa($vendedor->empresa_id)->lockForUpdate()->findOrFail($reservaId);
            Reserva::exigir($reserva->estado_pago === Reserva::ESTADO_PAGO_PENDIENTE, 'reserva', 'Solo se pueden rechazar pagos pendientes.');

            return PagoReservaService::marcarPagoFallido($reserva->id);
        });
    }
}
