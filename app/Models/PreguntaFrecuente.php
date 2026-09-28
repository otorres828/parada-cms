<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PreguntaFrecuente extends ModelHelper
{
    protected $table = 'preguntas_frecuentes';

    protected $fillable = [
        'categoria_pregunta_frecuente_id',
        'pregunta',
        'slug',
        'resumen',
        'respuesta',
        'palabras_clave',
        'destacada',
        'orden',
        'estatus',
    ];

    protected function casts(): array
    {
        return [
            'categoria_pregunta_frecuente_id' => 'integer',
            'destacada' => 'boolean',
            'estatus' => 'integer',
            'orden' => 'integer',
        ];
    }

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(CategoriaPreguntaFrecuente::class, 'categoria_pregunta_frecuente_id');
    }

    public static function searchAdmin(string $search = '', array $filters = []): Builder
    {
        $query = self::query()->with('categoria');

        if ($search !== '') {
            $query->where(function ($query) use ($search) {
                $query->where('preguntas_frecuentes.id', ctype_digit($search) ? $search : -1);
                $query->orWhere('preguntas_frecuentes.pregunta', 'like', '%'.$search.'%');
                $query->orWhere('preguntas_frecuentes.resumen', 'like', '%'.$search.'%');
                $query->orWhere('preguntas_frecuentes.respuesta', 'like', '%'.$search.'%');
                $query->orWhere('preguntas_frecuentes.palabras_clave', 'like', '%'.$search.'%');
            });
        }

        if (! empty($filters['categoria_id'])) {
            $query->where('preguntas_frecuentes.categoria_pregunta_frecuente_id', $filters['categoria_id']);
        }

        if (($filters['destacada'] ?? '') !== '') {
            $query->where('preguntas_frecuentes.destacada', $filters['destacada']);
        }

        if (isset($filters['estatus']) && $filters['estatus'] !== '') {
            $query->where('preguntas_frecuentes.estatus', $filters['estatus']);
        } else {
            $query->where('preguntas_frecuentes.estatus', '!=', self::ESTADO_DELETE);
        }

        return $query;
    }
}
