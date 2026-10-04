<?php

namespace App\Services\Empresa;

use App\Models\Empresa;
use App\Models\Programacion;
use App\Models\Transporte;
use App\Models\Viaje;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ProgramacionService
{
    public static function fechas(array $configuracion): array
    {
        Validator::make($configuracion, [
            'modo' => ['required', 'in:unica,rango,especificas'],
            'desde' => ['required', 'date_format:Y-m-d', 'after_or_equal:today', 'before_or_equal:2100-12-31'],
            'hasta' => ['required_if:modo,rango', 'nullable', 'date_format:Y-m-d', 'after_or_equal:desde', 'before_or_equal:2100-12-31'],
            'dias' => ['required_if:modo,rango', 'array'],
            'dias.*' => ['integer', 'between:1,7', 'distinct'],
            'fechas' => ['required_if:modo,especificas', 'array', 'max:366'],
            'fechas.*' => ['required', 'date_format:Y-m-d', 'after_or_equal:today', 'before_or_equal:2100-12-31', 'distinct'],
        ], [
            'required' => 'Indica las fechas de programación.',
            'required_if' => 'Completa las fechas y los días de la modalidad seleccionada.',
            'in' => 'La modalidad de fechas no es válida.',
            'date_format' => 'La fecha debe tener formato año-mes-día.',
            'after_or_equal' => 'La fecha es anterior al inicio permitido.',
            'before_or_equal' => 'La fecha supera el año permitido.',
            'array' => 'Las fechas y días deben enviarse como una lista.',
            'integer' => 'El día de la semana no es válido.',
            'between' => 'Selecciona días de lunes a domingo.',
            'distinct' => 'No repitas fechas ni días de la semana.',
            'max' => 'Puedes crear hasta 366 programaciones por lote.',
        ])->validate();

        if ($configuracion['modo'] === 'unica') {
            return [$configuracion['desde']];
        }
        if ($configuracion['modo'] === 'especificas') {
            $fechas = $configuracion['fechas'];
            sort($fechas);
        } else {
            $inicio = Carbon::parse($configuracion['desde']);
            $fin = Carbon::parse($configuracion['hasta']);
            Programacion::exigir($inicio->diffInDays($fin) < 366, 'hasta', 'El rango puede abarcar hasta 366 días.');
            $fechas = [];
            for ($fecha = $inicio->copy(); $fecha->lte($fin); $fecha->addDay()) {
                if (in_array($fecha->dayOfWeekIso, array_map('intval', $configuracion['dias']), true)) {
                    $fechas[] = $fecha->format('Y-m-d');
                }
            }
        }
        Programacion::exigir(count($fechas) > 0, 'fechas', 'Selecciona al menos una fecha de salida.');

        return $fechas;
    }

    public static function guardarLote(int $empresaId, array $datos, array $configuracion): array
    {
        $fechas = self::fechas($configuracion);
        foreach ($datos['tramos'] ?? [] as $tramo) {
            if (! ($tramo['habilitado'] ?? false)) {
                continue;
            }
            Validator::make($tramo, [
                'fecha_salida' => ['required', 'date_format:Y-m-d'],
                'fecha_llegada' => ['required', 'date_format:Y-m-d'],
            ], [
                'required' => 'Completa las fechas del trayecto.',
                'date_format' => 'La fecha del trayecto no tiene un formato válido.',
            ])->validate();
            Programacion::exigir($tramo['fecha_salida'] >= $configuracion['desde'], 'tramos', 'Los trayectos no pueden salir antes de la fecha inicial de referencia.');
        }

        return DB::transaction(function () use ($empresaId, $datos, $configuracion, $fechas) {
            $ids = [];
            $referencia = Carbon::parse($configuracion['desde']);
            foreach ($fechas as $fecha) {
                $copia = $datos;
                $desplazamiento = (int) $referencia->diffInDays(Carbon::parse($fecha), false);
                foreach ($copia['tramos'] as &$tramo) {
                    if ($tramo['habilitado']) {
                        $tramo['fecha_salida'] = Carbon::parse($tramo['fecha_salida'])->addDays($desplazamiento)->format('Y-m-d');
                        $tramo['fecha_llegada'] = Carbon::parse($tramo['fecha_llegada'])->addDays($desplazamiento)->format('Y-m-d');
                    }
                }
                unset($tramo);
                $ids[] = self::guardar($empresaId, $copia)->id;
            }

            return $ids;
        });
    }

    public static function guardar(int $empresaId, array $datos, ?int $programacionId = null): Programacion
    {
        Validator::make($datos, [
            'viaje_id' => ['required', 'integer'],
            'transporte_id' => ['required', 'integer'],
            'estatus' => ['required', 'integer', 'in:1,2'],
            'tramos' => ['required', 'array', 'min:1'],
            'tramos.*.habilitado' => ['required', 'boolean'],
        ], [
            'required' => 'El campo :attribute es obligatorio.',
            'integer' => 'El campo :attribute debe ser un número entero.',
            'in' => 'El estatus seleccionado no es válido.',
            'array' => 'Los trayectos deben ser una lista.',
            'min' => 'Selecciona al menos un trayecto.',
            'boolean' => 'La selección del trayecto no es válida.',
        ])->validate();

        return DB::transaction(function () use ($empresaId, $datos, $programacionId) {
            $empresa = Empresa::findOrFail($empresaId);
            $viaje = Viaje::searchAdmin('', ['empresa_id' => $empresaId, 'status' => Viaje::ESTADO_ACTIVE])
                ->with('tramos')->findOrFail($datos['viaje_id']);
            $transporte = Transporte::searchAdmin('', [
                'empresa_id' => $empresaId,
                'status' => Transporte::ESTADO_ACTIVE,
                'tipo_transporte' => $empresa->getTipoTransporte(),
            ])->where('es_plantilla', false)->findOrFail($datos['transporte_id']);
            $programacion = $programacionId === null ? new Programacion : Programacion::searchAdmin('', [
                'empresa_id' => $empresaId,
            ])->lockForUpdate()->findOrFail($programacionId);

            Programacion::exigir(! $programacion->exists || in_array($programacion->estatus, [1, 2], true), 'estatus', 'Una programación finalizada no se puede editar.');
            Programacion::exigir(! $programacion->exists || ! $programacion->reservas()->exists(), 'tramos', 'Una programación con reservas no puede modificar su transporte, horarios ni tarifas.');
            $plantilla = $viaje->tramos->keyBy(function ($tramo) {
                return $tramo->origen_terminal_id.'-'.$tramo->destino_terminal_id;
            });
            $seleccionados = [];
            $salidas = [];
            $llegadas = [];

            foreach ($datos['tramos'] as $clave => $tramo) {
                Programacion::exigir($plantilla->has($clave), 'tramos', 'El trayecto no pertenece a la ruta seleccionada.');
                if (! $tramo['habilitado']) {
                    continue;
                }
                Validator::make($tramo, [
                    'precio' => ['required', 'numeric', 'decimal:0,2', 'min:0', 'max:9999999999.99'],
                    'fecha_salida' => ['required', 'date_format:Y-m-d', 'after_or_equal:today', 'before_or_equal:2100-12-31'],
                    'hora_salida' => ['required', 'date_format:H:i'],
                    'fecha_llegada' => ['required', 'date_format:Y-m-d', 'after_or_equal:fecha_salida', 'before_or_equal:2100-12-31'],
                    'hora_llegada' => ['required', 'date_format:H:i'],
                ], [
                    'required' => 'El campo :attribute es obligatorio en los trayectos seleccionados.',
                    'numeric' => 'El precio debe ser numérico.',
                    'decimal' => 'El precio permite hasta dos decimales.',
                    'min' => 'El precio no puede ser negativo.',
                    'max' => 'El precio excede el máximo permitido.',
                    'date_format' => 'El formato de :attribute no es válido.',
                    'after_or_equal' => 'La fecha de :attribute es anterior a la permitida.',
                    'before_or_equal' => 'La fecha supera el año máximo permitido.',
                ])->validate();
                $base = $plantilla[$clave];
                $salida = $tramo['fecha_salida'].' '.$tramo['hora_salida'];
                $llegada = $tramo['fecha_llegada'].' '.$tramo['hora_llegada'];
                Programacion::exigir(! isset($salidas[$base->origen_terminal_id]) || $salidas[$base->origen_terminal_id] === $salida, 'tramos', 'Los trayectos que parten del mismo terminal deben compartir fecha y hora de salida.');
                Programacion::exigir(! isset($llegadas[$base->destino_terminal_id]) || $llegadas[$base->destino_terminal_id] === $llegada, 'tramos', 'Los trayectos que llegan al mismo terminal deben compartir fecha y hora de llegada.');
                $salidas[$base->origen_terminal_id] = $salida;
                $llegadas[$base->destino_terminal_id] = $llegada;
                $seleccionados[] = [
                    'origen_terminal_id' => $base->origen_terminal_id,
                    'destino_terminal_id' => $base->destino_terminal_id,
                    'precio' => $tramo['precio'],
                    'fecha_salida' => $tramo['fecha_salida'],
                    'hora_salida' => $tramo['hora_salida'],
                    'fecha_llegada' => $tramo['fecha_llegada'],
                    'hora_llegada' => $tramo['hora_llegada'],
                ];
            }

            Programacion::exigir(count($seleccionados) > 0, 'tramos', 'Selecciona al menos un trayecto para vender.');
            $programacion->fill([
                'viaje_id' => $viaje->id,
                'transporte_id' => $transporte->id,
                'asientos_totales' => $transporte->total_asientos,
                'estatus' => $datos['estatus'],
            ])->save();
            $programacion->tramoPrecios()->delete();
            $programacion->tramoPrecios()->createMany($seleccionados);

            return $programacion->load('tramoPrecios');
        });
    }
}
