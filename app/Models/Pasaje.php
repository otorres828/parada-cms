<?php

namespace App\Models;

use App\Traits\TraitGeneral;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pasaje extends ModelHelper
{
    use TraitGeneral;

    protected $table = 'pasajes';

    protected $fillable = [
        'tipo_servicio',
        'valor_servicio',
        'base_tasa_servicio',
        'tasa_servicio',
        'tasa_servicio_id',
        'reserva_id',
        'viajero_id',
        'origen_terminal_id',
        'destino_terminal_id',
        'programacion_tramo_precio_id',
        'numero_asiento',
        'precio_base',
        'descuento',
        'precio_final',
        'codigo_qr_token',
        'abordado',
        'fecha_abordaje',
    ];

    protected function casts(): array
    {
        return ['tipo_servicio' => 'integer', 'valor_servicio' => 'decimal:2', 'base_tasa_servicio' => 'decimal:2', 'tasa_servicio' => 'decimal:2', 'numero_asiento' => 'integer', 'precio_base' => 'decimal:2', 'descuento' => 'decimal:2', 'precio_final' => 'decimal:2', 'abordado' => 'boolean', 'fecha_abordaje' => 'datetime'];
    }

    public function reserva(): BelongsTo
    {
        return $this->belongsTo(Reserva::class, 'reserva_id');
    }

    public function tarifaServicio(): BelongsTo
    {
        return $this->belongsTo(TasaServicio::class, 'tasa_servicio_id');
    }

    public function viajero(): BelongsTo
    {
        return $this->belongsTo(Viajero::class, 'viajero_id');
    }

    public function origenTerminal(): BelongsTo
    {
        return $this->belongsTo(Terminal::class, 'origen_terminal_id');
    }

    public function destinoTerminal(): BelongsTo
    {
        return $this->belongsTo(Terminal::class, 'destino_terminal_id');
    }

    public function tramoPrecio(): BelongsTo
    {
        return $this->belongsTo(ProgramacionTramoPrecio::class, 'programacion_tramo_precio_id');
    }

    public static function searchAdmin(string $search = '', array $filters = []): Builder
    {
        $query = self::query()->with(['reserva', 'viajero', 'origenTerminal', 'destinoTerminal']);

        if ($search !== '') {
            $query->where(function ($query) use ($search) {
                $query->where('pasajes.id', ctype_digit($search) ? $search : -1);
                $query->orWhereHas('reserva', fn ($q) => $q->where('codigo_referencia', 'like', '%' . $search . '%'))->orWhereHas('viajero', fn ($q) => $q->where('nombre', 'like', '%' . $search . '%')->orWhere('documento_identidad', 'like', '%' . $search . '%'));
            });
        }

        if (isset($filters['reserva_id'])) {
            $query->where('pasajes.reserva_id', $filters['reserva_id']);
        }
        $reservationFilters = array_intersect_key($filters, array_flip(['empresa_id', 'date_from', 'date_to', 'estado_pago']));

        if ($reservationFilters) {
            $query->whereHas('reserva', fn ($q) => $q->mergeConstraintsFrom(Reserva::searchAdmin('', $reservationFilters)));
        }

        return $query;
    }

    public static function searchDetailProgramacion(int $programacion_id): Collection
    {
        return self::searchAdmin()
            ->whereHas('reserva', fn ($q) => 
                $q->where('programacion_id', $programacion_id)
                ->whereIn('estado_pago', [Reserva::ESTADO_PAGO_PAGADO, Reserva::ESTADO_PAGO_PENDIENTE])
            )->get();
    }
}
