<?php

namespace App\Services;

use App\Models\ConfiguracionCupon;
use App\Models\Cupon;
use App\Models\Reserva;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CuponService
{
    public function validarExistenciaYDisponibilidad(string $codigo, int $empresaId): array
    {
        return DB::transaction(function () use ($codigo, $empresaId) {
            return $this->buscarDisponible($codigo, $empresaId);
        }, 3);
    }

    public function aplicarCupon(Reserva $reserva, string $codigo): Reserva
    {
        return DB::transaction(function () use ($reserva, $codigo) {
            $reserva = Reserva::with(['programacion.viaje', 'usuario'])->whereKey($reserva->id)->lockForUpdate()->firstOrFail();
            $this->validarReservaEditable($reserva);
            $pasajes = $reserva->pasajes()->orderBy('id')->lockForUpdate()->get();
            $this->exigir($pasajes->isNotEmpty(), 'cupon', 'Agrega al menos un pasajero antes de aplicar el cupón.');

            $codigo = strtoupper(trim($codigo));
            if ($reserva->cupon_id) {
                $actual = Cupon::whereKey($reserva->cupon_id)->lockForUpdate()->first();
                if ($actual && strtoupper($actual->codigo) === $codigo) {
                    return $reserva->fresh(['cupon', 'pasajes']);
                }
                $this->liberar($reserva);
                $reserva->refresh();
            }

            $empresaId = (int) $reserva->programacion->viaje->empresa_id;
            ['campana' => $campana, 'cupon' => $cupon] = $this->buscarDisponible($codigo, $empresaId);
            $this->validarModalidad($campana, $reserva);

            if ($campana->tipo_cupon === ConfiguracionCupon::TIPO_PERSONALIZADO) {
                $cupon = Cupon::create([
                    'configuracion_cupon_id' => $campana->id,
                    'usuario_id' => $reserva->usuario_id,
                    'codigo' => $campana->codigo_personalizado,
                    'redimido' => true,
                    'fecha_redencion' => now(),
                ]);
            } else {
                $cupon->update([
                    'usuario_id' => $reserva->usuario_id,
                    'redimido' => true,
                    'fecha_redencion' => now(),
                ]);
            }

            $base = round((float) $pasajes->sum('precio_base'), 2);
            $descuentoReserva = $campana->aplica_en === ConfiguracionCupon::APLICA_EN_RESERVA
                ? $this->calcularDescuento($base, $campana)
                : 0.0;
            $restante = $descuentoReserva;
            $descuentoAplicado = 0.0;
            $ultimo = $pasajes->count() - 1;

            foreach ($pasajes as $indice => $pasaje) {
                if ($campana->aplica_en === ConfiguracionCupon::APLICA_EN_PASAJES) {
                    $descuento = $this->calcularDescuento((float) $pasaje->precio_base, $campana);
                } elseif ($indice === $ultimo) {
                    $descuento = $restante;
                } else {
                    $descuento = $base > 0 ? round($descuentoReserva * (float) $pasaje->precio_base / $base, 2) : 0.0;
                    $restante = round($restante - $descuento, 2);
                }

                $subtotal = round((float) $pasaje->precio_base - $descuento, 2);
                $pasaje->update([
                    'descuento' => number_format($descuento, 2, '.', ''),
                    'subtotal' => number_format($subtotal, 2, '.', ''),
                    'tasa_servicio' => '0.00',
                    'total' => number_format($subtotal, 2, '.', ''),
                    'servicio_json' => null,
                ]);
                $descuentoAplicado = round($descuentoAplicado + $descuento, 2);
            }

            $reserva->cupon_id = $cupon->id;
            $reserva->monto_pasajes = number_format($base, 2, '.', '');
            $reserva->descuento_aplicado = number_format($descuentoAplicado, 2, '.', '');
            $reserva->tasa_servicio = '0.00';
            $reserva->monto_total = number_format($base - $descuentoAplicado, 2, '.', '');
            $this->auditar($reserva, 'aplicado', $campana, $cupon, $descuentoAplicado);
            $reserva->save();

            return $reserva->fresh(['cupon.configuracionCupon', 'pasajes']);
        }, 3);
    }

    public function removerCupon(Reserva $reserva): Reserva
    {
        return DB::transaction(function () use ($reserva) {
            $reserva = Reserva::whereKey($reserva->id)->lockForUpdate()->firstOrFail();
            $this->validarReservaEditable($reserva);
            $this->liberar($reserva);

            return $reserva->fresh(['pasajes']);
        }, 3);
    }

    public function cancelarYLiberarCupon(Reserva $reserva): void
    {
        DB::transaction(function () use ($reserva) {
            $reserva = Reserva::whereKey($reserva->id)->lockForUpdate()->firstOrFail();
            $this->liberar($reserva);
        }, 3);
    }

    public function validarCuponAplicado(Reserva $reserva): void
    {
        if (! $reserva->cupon_id) {
            return;
        }

        $cupon = Cupon::with('configuracionCupon')->whereKey($reserva->cupon_id)->lockForUpdate()->first();
        $this->exigir($cupon && $cupon->redimido && (int) $cupon->usuario_id === (int) $reserva->usuario_id, 'cupon', 'El cupón aplicado ya no está disponible.');
        $this->exigir($cupon->configuracionCupon !== null, 'cupon', 'La campaña del cupón no existe.');
    }

    private function buscarDisponible(string $codigo, int $empresaId): array
    {
        $codigo = strtoupper(trim($codigo));
        $cupon = Cupon::whereRaw('UPPER(codigo) = ?', [$codigo])
            ->whereHas('configuracionCupon', function ($query) {
                $query->where('tipo_cupon', ConfiguracionCupon::TIPO_RANDOM);
            })
            ->lockForUpdate()
            ->first();
        $campana = $cupon
            ? ConfiguracionCupon::whereKey($cupon->configuracion_cupon_id)->lockForUpdate()->first()
            : ConfiguracionCupon::where('tipo_cupon', ConfiguracionCupon::TIPO_PERSONALIZADO)
                ->whereRaw('UPPER(codigo_personalizado) = ?', [$codigo])->lockForUpdate()->first();

        $this->exigir($campana !== null, 'cupon', 'El cupón no existe.');
        $this->exigir($campana->estatus === 1 && $campana->fecha_inicio->lte(now()) && $campana->fecha_fin->gte(now()), 'cupon', 'La campaña no está vigente.');
        $this->exigir($campana->empresa_id === null || (int) $campana->empresa_id === $empresaId, 'cupon', 'El cupón corresponde a otra empresa.');
        $usados = Cupon::where('configuracion_cupon_id', $campana->id)->where('redimido', true)->lockForUpdate()->count();
        $this->exigir($usados < $campana->cantidad_generar, 'cupon', 'El cupón alcanzó su límite de usos.');

        if ($campana->tipo_cupon === ConfiguracionCupon::TIPO_RANDOM) {
            $this->exigir($cupon !== null && ! $cupon->redimido && $cupon->usuario_id === null, 'cupon', 'El cupón ya no está disponible.');
        } else {
            $this->exigir($cupon === null, 'cupon', 'El cupón ya está en uso.');
        }

        return ['campana' => $campana, 'cupon' => $cupon];
    }

    private function validarModalidad(ConfiguracionCupon $campana, Reserva $reserva): void
    {
        if ($campana->modalidad === ConfiguracionCupon::MODALIDAD_PRIMERA_COMPRA) {
            $tieneCompra = Reserva::where('usuario_id', $reserva->usuario_id)->whereKeyNot($reserva->id)
                ->whereIn('estado_pago', [Reserva::ESTADO_PAGO_PAGADO, Reserva::ESTADO_PAGO_REEMBOLSADO])->exists();
            $this->exigir(! $tieneCompra, 'cupon', 'Este cupón solo aplica a la primera compra.');
        }

        if ($campana->modalidad === ConfiguracionCupon::MODALIDAD_USUARIO_NUEVO) {
            $this->exigir($reserva->usuario && Carbon::parse($reserva->usuario->created_at)->gte($campana->fecha_inicio), 'cupon', 'Este cupón solo aplica a usuarios nuevos de la campaña.');
        }
    }

    private function liberar(Reserva $reserva): void
    {
        $cupon = $reserva->cupon_id ? Cupon::with('configuracionCupon')->whereKey($reserva->cupon_id)->lockForUpdate()->first() : null;
        if ($cupon) {
            $this->auditar($reserva, 'liberado', $cupon->configuracionCupon, $cupon, (float) $reserva->descuento_aplicado);
            $reserva->cupon_id = null;
            $reserva->save();
            if ($cupon->configuracionCupon?->tipo_cupon === ConfiguracionCupon::TIPO_PERSONALIZADO) {
                $cupon->delete();
            } else {
                $cupon->update(['usuario_id' => null, 'redimido' => false, 'fecha_redencion' => null]);
            }
        }

        $base = 0.0;
        foreach ($reserva->pasajes()->lockForUpdate()->get() as $pasaje) {
            $precio = round((float) $pasaje->precio_base, 2);
            $pasaje->update(['descuento' => '0.00', 'subtotal' => number_format($precio, 2, '.', ''), 'tasa_servicio' => '0.00', 'total' => number_format($precio, 2, '.', ''), 'servicio_json' => null]);
            $base = round($base + $precio, 2);
        }
        $reserva->update(['cupon_id' => null, 'monto_pasajes' => number_format($base, 2, '.', ''), 'descuento_aplicado' => '0.00', 'tasa_servicio' => '0.00', 'monto_total' => number_format($base, 2, '.', '')]);
    }

    private function calcularDescuento(float $base, ConfiguracionCupon $campana): float
    {
        $valor = (float) $campana->monto_descuento;
        $descuento = $campana->tipo_descuento === 'porcentaje' ? round($base * $valor / 100, 2) : round($valor, 2);

        return min($base, max(0, $descuento));
    }

    private function auditar(Reserva $reserva, string $evento, ?ConfiguracionCupon $campana, Cupon $cupon, float $descuento): void
    {
        $auditoria = $reserva->comentarios_auditoria ?? [];
        $auditoria['cupones'][] = ['evento' => $evento, 'fecha' => now()->toIso8601String(), 'cupon_id' => $cupon->id, 'configuracion_cupon_id' => $campana?->id, 'codigo' => $cupon->codigo, 'tipo_cupon' => $campana?->tipo_cupon, 'tipo_descuento' => $campana?->tipo_descuento, 'aplica_en' => $campana?->aplica_en, 'modalidad' => $campana?->modalidad, 'valor' => $campana?->monto_descuento, 'descuento_aplicado' => number_format($descuento, 2, '.', '')];
        $reserva->comentarios_auditoria = $auditoria;
    }

    private function validarReservaEditable(Reserva $reserva): void
    {
        $this->exigir($reserva->estado_pago === Reserva::ESTADO_PAGO_NUEVO && $reserva->fecha_expiracion?->isFuture() && ! $reserva->pago()->exists(), 'reserva', 'Solo se puede modificar el cupón de una reserva nueva y vigente.');
    }

    private function exigir(bool $condicion, string $campo, string $mensaje): void
    {
        if (! $condicion) {
            throw ValidationException::withMessages([$campo => $mensaje]);
        }
    }
}
