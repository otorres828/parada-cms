<?php

namespace App\Services\Empresa;

use App\Models\Terminal;
use App\Models\UsuarioEmpresa;
use App\Models\Viaje;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ViajeService
{
    public static function guardar(UsuarioEmpresa $usuario, ?int $viajeId, array $paradas, array $precios, array $minutos, string $comentario, int $estatus): Viaje
    {
        $datos = Validator::make(compact('paradas', 'precios', 'minutos', 'comentario', 'estatus'), [
            'paradas' => ['required', 'array', 'min:2', 'max:20'],
            'paradas.*' => ['required', 'integer', 'distinct', Rule::exists('terminales', 'id')->where('estatus', Terminal::ESTADO_ACTIVE)],
            'precios' => ['required', 'array'],
            'precios.*' => ['required', 'numeric', 'decimal:0,2', 'min:0', 'max:9999999999.99'],
            'minutos' => ['required', 'array'],
            'minutos.*' => ['required', 'integer', 'min:1', 'max:1440'],
            'comentario' => ['nullable', 'string', 'max:5000'],
            'estatus' => ['required', 'integer', 'in:1,2'],
        ], [
            'required' => 'El campo :attribute es obligatorio.',
            'array' => 'El campo :attribute debe ser una lista válida.',
            'integer' => 'El campo :attribute debe ser un número entero.',
            'distinct' => 'No puedes repetir un terminal en el recorrido.',
            'exists' => 'Selecciona un terminal activo.',
            'numeric' => 'Indica un precio válido.',
            'decimal' => 'El precio admite hasta dos decimales.',
            'min' => 'El campo :attribute no cumple el mínimo de :min.',
            'max' => 'El campo :attribute supera el máximo de :max.',
            'string' => 'El comentario debe ser texto.',
            'in' => 'Selecciona un estatus válido.',
        ])->validate();

        return DB::transaction(function () use ($usuario, $viajeId, $datos) {
            $paradas = array_values(array_map('intval', $datos['paradas']));
            $viaje = $viajeId === null ? new Viaje : Viaje::where('empresa_id', $usuario->empresa_id)->lockForUpdate()->findOrFail($viajeId);
            if ($viaje->exists) {
                $claves = array_column(Viaje::combinaciones($paradas), 'clave');
                foreach ($viaje->tramos as $tramo) {
                    Viaje::exigir(in_array($tramo->origen_terminal_id.'-'.$tramo->destino_terminal_id, $claves, true), 'paradas', 'No puedes eliminar ni invertir los tramos existentes de una ruta.');
                }
                Viaje::exigir($paradas[0] === (int) $viaje->origen_terminal_id && end($paradas) === (int) $viaje->destino_terminal_id, 'paradas', 'No puedes modificar el origen ni el destino de una ruta existente.');
                Viaje::exigir($paradas === $viaje->secuenciaTerminales() || ! $viaje->programaciones()->exists(), 'paradas', 'Esta ruta ya tiene programaciones. Conserva sus paradas o crea una nueva ruta.');
            }
            $duraciones = [0];
            for ($i = 0; $i < count($paradas) - 1; $i++) {
                $clave = $paradas[$i].'-'.$paradas[$i + 1];
                Viaje::exigir(isset($datos['minutos'][$clave]), 'minutos', 'Indica la duración entre cada par de paradas consecutivas.');
                $duraciones[] = $duraciones[$i] + (int) $datos['minutos'][$clave];
            }
            $viaje->fill([
                'empresa_id' => $usuario->empresa_id,
                'origen_terminal_id' => $paradas[0],
                'destino_terminal_id' => end($paradas),
                'duracion_estimada' => self::hora(end($duraciones)),
                'comentario' => $datos['comentario'],
                'estatus' => $datos['estatus'],
            ])->save();
            foreach (Viaje::combinaciones($paradas) as $orden => $tramo) {
                Viaje::exigir(isset($datos['precios'][$tramo['clave']]), 'precios', 'Indica el precio base de todas las combinaciones.');
                $viaje->tramos()->updateOrCreate([
                    'origen_terminal_id' => $tramo['origen_terminal_id'],
                    'destino_terminal_id' => $tramo['destino_terminal_id'],
                ], [
                    'orden' => $orden + 1,
                    'posicion_origen' => $tramo['posicion_origen'],
                    'posicion_destino' => $tramo['posicion_destino'],
                    'precio' => $datos['precios'][$tramo['clave']],
                    'duracion_estimada' => self::hora($duraciones[$tramo['posicion_destino']] - $duraciones[$tramo['posicion_origen']]),
                ]);
            }

            return $viaje->refresh();
        });
    }

    private static function hora(int $minutos): string
    {
        return sprintf('%02d:%02d:00', intdiv($minutos, 60), $minutos % 60);
    }
}
