<?php

namespace App\Models;

use App\Traits\TraitGeneral;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Viaje extends ModelHelper
{
    use TraitGeneral;

    protected $table = 'viajes';

    protected $fillable = [
        'empresa_id',
        'origen_terminal_id',
        'destino_terminal_id',
        'duracion_estimada',
        'estatus',
    ];

    protected function casts(): array
    {
        return ['estatus' => 'boolean'];
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function origenTerminal(): BelongsTo
    {
        return $this->belongsTo(Terminal::class, 'origen_terminal_id');
    }

    public function destinoTerminal(): BelongsTo
    {
        return $this->belongsTo(Terminal::class, 'destino_terminal_id');
    }

    public function programaciones(): HasMany
    {
        return $this->hasMany(Programacion::class, 'viaje_id');
    }

    public function reservas(): HasManyThrough
    {
        return $this->hasManyThrough(Reserva::class, Programacion::class, 'viaje_id', 'programacion_id');
    }

    public static function searchAdmin(string $search = '', array $filters = []): Builder
    {
        $query = self::query()->with([0 => 'empresa', 1 => 'origenTerminal', 2 => 'destinoTerminal']);

        if ($search !== '') {
            $query->where(function ($query) use ($search) {
                $query->where('viajes.id', ctype_digit($search) ? $search : -1);
                $query->orWhereHas('empresa', fn ($q) => $q->where('nombre', 'like', '%' . $search . '%'));
            });
        }

        $status = $filters['status'] ?? ($filters['estatus'] ?? ($filters['estado_pago'] ?? null));

        if ($status !== null && $status !== '') {
            $query->where('viajes.estatus', $status);
        }

        if (isset($filters['empresa_id']) && $filters['empresa_id'] !== '') {
            $query->where('viajes.empresa_id', $filters['empresa_id']);
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate('viajes.created_at', '>=', self::date($filters['date_from']));
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('viajes.created_at', '<=', self::date($filters['date_to']));
        }

        if (!empty($filters['con_tasas'])) {
            $query->withSum(['reservas as tasas_servicio_total' => fn ($q) => $q->where('estado_pago', Reserva::ESTADO_PAGO_PAGADO)], 'tasa_servicio');
        }

        return $query;
    }
}
