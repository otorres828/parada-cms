<?php

namespace App\Models;

use App\Traits\TraitGeneral;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Terminal extends ModelHelper
{
    use TraitGeneral;

    protected $table = 'terminales';

    protected $fillable = [
        'estado_id',
        'nombre',
        'direccion',
        'latitud',
        'longitud',
        'estatus',
    ];

    protected function casts(): array
    {
        return ['latitud' => 'decimal:7', 'longitud' => 'decimal:7', 'estatus' => 'boolean'];
    }

    public function estado(): BelongsTo
    {
        return $this->belongsTo(Estado::class, 'estado_id');
    }

    public function viajesOrigen(): HasMany
    {
        return $this->hasMany(Viaje::class, 'origen_terminal_id');
    }

    public function viajesDestino(): HasMany
    {
        return $this->hasMany(Viaje::class, 'destino_terminal_id');
    }

    public static function searchAdmin(string $search = '', array $filters = []): Builder
    {
        $query = self::query()->with([0 => 'estado']);

        if ($search !== '') {
            $query->where(function ($query) use ($search) {
                $query->where('terminales.id', ctype_digit($search) ? $search : -1);
                $query->orWhere('terminales.nombre', 'like', '%' . $search . '%');
                $query->orWhere('terminales.direccion', 'like', '%' . $search . '%');
            });
        }

        $status = $filters['status'] ?? $filters['estatus'] ?? $filters['estado_pago'] ?? null;

        if ($status !== null && $status !== '') {
            $query->where('terminales.estatus', $status);
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate('terminales.created_at', '>=', self::date($filters['date_from']));
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('terminales.created_at', '<=', self::date($filters['date_to']));
        }

        return $query;
    }
}
