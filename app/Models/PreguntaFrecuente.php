<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;

class PreguntaFrecuente extends ModelHelper
{
    protected $table = 'preguntas_frecuentes';

    protected $fillable = ['pregunta', 'respuesta', 'orden', 'estatus'];

    protected function casts(): array
    {
        return [
            'estatus' => 'integer',
            'orden' => 'integer',
        ];
    }

    public static function searchAdmin(string $search = '', array $filters = []): Builder
    {
        $query = self::query();

        if ($search !== '') {
            $query->where(function ($query) use ($search) {
                $query->where('preguntas_frecuentes.id', ctype_digit($search) ? $search : -1);
                $query->orWhere('preguntas_frecuentes.pregunta', 'like', '%'.$search.'%');
                $query->orWhere('preguntas_frecuentes.respuesta', 'like', '%'.$search.'%');
            });
        }

        if (isset($filters['estatus']) && $filters['estatus'] !== '') {
            $query->where('preguntas_frecuentes.estatus', $filters['estatus']);
        } else {
            $query->where('preguntas_frecuentes.estatus', '!=', self::ESTADO_DELETE);
        }

        return $query;
    }
}
