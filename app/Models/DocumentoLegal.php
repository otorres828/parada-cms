<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentoLegal extends ModelHelper
{
    public const TIPOS = ['contrato' => 'Contrato', 'renovacion' => 'Renovación de contrato', 'acuerdo' => 'Acuerdo', 'licencia' => 'Licencia', 'otro' => 'Otro documento'];

    protected $table = 'documentos_legales';

    protected $fillable = [
        'empresa_id',
        'admin_id',
        'titulo',
        'tipo',
        'observaciones',
        'archivo',
        'nombre_original',
        'mime',
        'tamano',
    ];

    protected function casts(): array
    {
        return ['tamano' => 'integer'];
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }

    public static function searchAdmin(string $search = '', array $filters = []): Builder
    {
        $query = self::query()->with('admin');

        if (isset($filters['empresa_id'])) {
            $query->where('empresa_id', $filters['empresa_id']);
        }

        if (!empty($filters['tipo'])) {
            $query->where('tipo', $filters['tipo']);
        }

        if ($search !== '') {
            $query->where(fn ($q) => $q->where('titulo', 'like', '%' . $search . '%')->orWhere('nombre_original', 'like', '%' . $search . '%')->orWhere('observaciones', 'like', '%' . $search . '%'));
        }

        return $query;
    }
}
