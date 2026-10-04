<?php

namespace App\Services\Empresa;

use App\Models\Empresa;
use App\Models\Programacion;
use App\Models\Transporte;
use App\Models\Viaje;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ProgramacionService
{
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
