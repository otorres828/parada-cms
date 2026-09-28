<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CategoriaPreguntaFrecuente extends ModelHelper
{
    protected $table = 'categorias_preguntas_frecuentes';

    protected $fillable = [
        'nombre',
        'slug',
        'descripcion',
        'icono',
        'imagen',
        'destacada',
        'orden',
        'estatus',
    ];

    protected function casts(): array
    {
        return [
            'destacada' => 'boolean',
            'orden' => 'integer',
            'estatus' => 'integer',
        ];
    }

    public function preguntas(): HasMany
    {
        return $this->hasMany(PreguntaFrecuente::class, 'categoria_pregunta_frecuente_id');
    }

    public static function searchAdmin(string $search = '', array $filters = []): Builder
    {
        $query = self::query()->withCount([
            'preguntas' => function ($query) {
                $query->where('estatus', '!=', self::ESTADO_DELETE);
            },
        ]);

        if ($search !== '') {
            $query->where(function ($query) use ($search) {
                $query->where('categorias_preguntas_frecuentes.id', ctype_digit($search) ? $search : -1);
                $query->orWhere('categorias_preguntas_frecuentes.nombre', 'like', '%'.$search.'%');
                $query->orWhere('categorias_preguntas_frecuentes.descripcion', 'like', '%'.$search.'%');
            });
        }

        if (($filters['estatus'] ?? '') !== '') {
            $query->where('categorias_preguntas_frecuentes.estatus', $filters['estatus']);
        } else {
            $query->where('categorias_preguntas_frecuentes.estatus', '!=', self::ESTADO_DELETE);
        }

        return $query;
    }
}
