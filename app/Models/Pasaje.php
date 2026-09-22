<?php

namespace App\Models;

use App\Traits\TraitGeneral;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Pasaje extends ModelHelper
{
    use TraitGeneral;

    protected $table = 'pasajes';

    protected $fillable = [
        'tipo_servicio',
        'valor_servicio',
        'base_tasa_servicio',
        'tasa_servicio',
        'tasa_monto_minimo',
        'tasa_monto_maximo',
        'reserva_id',
        'viajero_id',
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
        return ['tipo_servicio' => 'integer', 'valor_servicio' => 'decimal:2', 'base_tasa_servicio' => 'decimal:2', 'tasa_monto_minimo' => 'decimal:2', 'tasa_monto_maximo' => 'decimal:2', 'tasa_servicio' => 'decimal:2', 'numero_asiento' => 'integer', 'precio_base' => 'decimal:2', 'descuento' => 'decimal:2', 'precio_final' => 'decimal:2', 'abordado' => 'boolean', 'fecha_abordaje' => 'datetime'];
    }

    protected static function booted(): void
    {
        static::creating(function (Pasaje $pasaje) {
            if (empty($pasaje->codigo_qr_token)) {
                $pasaje->codigo_qr_token = (string) Str::uuid();
            }
        });
    }

    public function getQr(): ?string
    {
        if ($this->reserva?->estado_pago !== Reserva::ESTADO_PAGO_PAGADO || empty($this->codigo_qr_token)) {
            return null;
        }

        $writer = new Writer(new ImageRenderer(new RendererStyle(256, 4), new SvgImageBackEnd));

        return 'data:image/svg+xml;base64,'.base64_encode($writer->writeString($this->codigo_qr_token));
    }

    public function reserva(): BelongsTo
    {
        return $this->belongsTo(Reserva::class, 'reserva_id');
    }

    public function viajero(): BelongsTo
    {
        return $this->belongsTo(Viajero::class, 'viajero_id');
    }

    public static function searchAdmin(string $search = '', array $filters = []): Builder
    {
        $query = self::query()->with(['reserva.origenTerminal', 'reserva.destinoTerminal', 'reserva.programacion', 'viajero']);

        if ($search !== '') {
            $query->where(function ($query) use ($search) {
                $query->where('pasajes.id', ctype_digit($search) ? $search : -1);
                $query->orWhereHas('reserva', function ($query) use ($search) {
                    return $query->where('codigo_referencia', 'like', '%' . $search . '%');
                })
                    ->orWhereHas('viajero', function ($query) use ($search) {
                        return $query->where('nombre', 'like', '%' . $search . '%')->orWhere('documento_identidad', 'like', '%' . $search . '%');
                    });
            });
        }

        if (isset($filters['reserva_id'])) {
            $query->where('pasajes.reserva_id', $filters['reserva_id']);
        }

        if (!empty($filters['empresa_id']) || !empty($filters['date_from']) || !empty($filters['date_to'])
            || (isset($filters['estado_pago']) && $filters['estado_pago'] !== '')) {
            $query->whereHas('reserva', function ($query) use ($filters) {
                if (!empty($filters['date_from'])) {
                    $query->whereDate('reservas.fecha_compra', '>=', self::date($filters['date_from']));
                }

                if (!empty($filters['date_to'])) {
                    $query->whereDate('reservas.fecha_compra', '<=', self::date($filters['date_to']));
                }

                if (!empty($filters['empresa_id'])) {
                    $query->whereHas('programacion.viaje', function ($query) use ($filters) {
                        return $query->where('empresa_id', $filters['empresa_id']);
                    });
                }

                if (isset($filters['estado_pago']) && $filters['estado_pago'] !== '') {
                    $query->where('reservas.estado_pago', $filters['estado_pago']);
                }
            });
        }

        return $query;
    }

    public static function getTickets(int $programacion_id): Collection
    {
        return self::searchAdmin()
            ->whereHas('reserva', fn ($q) => 
                $q->where('programacion_id', $programacion_id)
                ->whereIn('estado_pago', [
                    Reserva::ESTADO_PAGO_NUEVO,
                    Reserva::ESTADO_PAGO_PAGADO, 
                    Reserva::ESTADO_PAGO_PENDIENTE
                ])
            )->get();
    }
}
