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
        'reserva_id',
        'viajero_id',
        'numero_asiento',
        'precio_base',
        'descuento',
        'subtotal',
        'tasa_servicio',
        'total',
        'servicio_json',
        'localizador',
        'abordado',
        'hora_abordaje',
    ];

    protected function casts(): array
    {
        return ['numero_asiento' => 'integer', 'precio_base' => 'decimal:2', 'descuento' => 'decimal:2', 'subtotal' => 'decimal:2', 'tasa_servicio' => 'decimal:2', 'total' => 'decimal:2', 'servicio_json' => 'array', 'abordado' => 'boolean'];
    }

    public function reserva(): BelongsTo
    {
        return $this->belongsTo(Reserva::class, 'reserva_id');
    }

    public function viajero(): BelongsTo
    {
        return $this->belongsTo(Viajero::class, 'viajero_id');
    }

    public function getTipoServicioAttribute(): ?int
    {
        $value = $this->servicio('tipo_servicio');

        return $value === null ? null : (int) $value;
    }

    public function getValorAttribute(): ?string
    {
        return $this->servicio('valor');
    }

    public function getMontoMinimoAttribute(): ?string
    {
        return $this->servicio('monto_minimo');
    }

    public function getMontoMaximoAttribute(): ?string
    {
        return $this->servicio('monto_maximo');
    }

    protected function servicio(string $campo): mixed
    {
        return $this->servicio_json[$campo] ?? null;
    }

    public function getQr(): ?string
    {
        if ($this->reserva?->estado_pago !== Reserva::ESTADO_PAGO_PAGADO || empty($this->localizador)) {
            return null;
        }

        $writer = new Writer(new ImageRenderer(new RendererStyle(256, 4), new SvgImageBackEnd));

        return 'data:image/svg+xml;base64,'.base64_encode($writer->writeString($this->localizador));
    }

    protected static function booted(): void
    {
        static::creating(function (Pasaje $pasaje) {
            if (empty($pasaje->localizador)) {
                $pasaje->localizador = (string) Str::uuid();
            }
        });
    }

    public static function searchAdmin(string $search = '', array $filters = []): Builder
    {
        $query = self::query()->with(['reserva.origenTerminal', 'reserva.destinoTerminal', 'reserva.programacion', 'viajero']);

        if ($search !== '') {
            $query->where(function ($query) use ($search) {
                $query->where('pasajes.id', ctype_digit($search) ? $search : -1);
                $query->orWhereHas('reserva', function ($query) use ($search) {
                    return $query->where('codigo_referencia', 'like', '%'.$search.'%');
                })
                    ->orWhereHas('viajero', function ($query) use ($search) {
                        return $query->where('nombre', 'like', '%'.$search.'%')->orWhere('documento_identidad', 'like', '%'.$search.'%');
                    });
            });
        }

        if (isset($filters['reserva_id'])) {
            $query->where('pasajes.reserva_id', $filters['reserva_id']);
        }

        if (! empty($filters['empresa_id']) || ! empty($filters['date_from']) || ! empty($filters['date_to'])
            || (isset($filters['estado_pago']) && $filters['estado_pago'] !== '')) {
            $query->whereHas('reserva', function ($query) use ($filters) {
                if (! empty($filters['date_from'])) {
                    $query->whereDate('reservas.fecha_compra', '>=', self::date($filters['date_from']));
                }

                if (! empty($filters['date_to'])) {
                    $query->whereDate('reservas.fecha_compra', '<=', self::date($filters['date_to']));
                }

                if (! empty($filters['empresa_id'])) {
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
            ->whereHas('reserva', fn ($q) => $q->where('programacion_id', $programacion_id)
                ->whereIn('estado_pago', [
                    Reserva::ESTADO_PAGO_NUEVO,
                    Reserva::ESTADO_PAGO_PAGADO,
                    Reserva::ESTADO_PAGO_PENDIENTE,
                ])
            )->get();
    }

    public static function disponibilidad(
        Programacion $programacion,
        ProgramacionTramoPrecio $tarifa,
        array $terminales,
        ?int $excluirReservaId = null,
        ?Collection $reservas = null,
    ): array {
        [$inicio, $fin] = Terminal::obtenerIntervalo(
            $terminales,
            (int) $tarifa->origen_terminal_id,
            (int) $tarifa->destino_terminal_id,
        );

        $reservas ??= Reserva::reservasQueBloqueanAsientos()
            ->where('programacion_id', $programacion->id)
            ->when($excluirReservaId !== null, function ($query) use ($excluirReservaId) {
                $query->whereKeyNot($excluirReservaId);
            })
            ->with('pasajes')
            ->get();

        $ocupados = [];

        foreach ($reservas as $reserva) {
            if ($reserva->origen_terminal_id === null || $reserva->destino_terminal_id === null) {
                $solapa = true;
            } else {
                [$origenReserva, $destinoReserva] = Terminal::obtenerIntervalo(
                    $terminales,
                    (int) $reserva->origen_terminal_id,
                    (int) $reserva->destino_terminal_id,
                );
                $solapa = $inicio < $destinoReserva && $origenReserva < $fin;
            }

            if ($solapa) {
                $ocupados = array_merge(
                    $ocupados,
                    $reserva->pasajes->pluck('numero_asiento')->filter()->all(),
                );
            }
        }

        $capacidadAutobus = (int) $programacion->autobus?->total_asientos;
        $capacidad = max(0, min((int) $programacion->asientos_totales, $capacidadAutobus));
        $libres = $capacidad > 0 ? array_values(array_diff(range(1, $capacidad), $ocupados)) : [];
        $cantidadOcupados = $capacidad - count($libres);
        $limite = $tarifa->asientos_maximos_permitidos === null
            ? $capacidad
            : min($capacidad, max(0, $tarifa->asientos_maximos_permitidos));
        $cupo = max(0, $limite - $cantidadOcupados);

        return [
            'asientos' => $cupo > 0 ? $libres : [],
            'cupo_tramo' => $cupo,
            'capacidad' => $limite,
            'ocupados' => $cantidadOcupados,
            'disponibles' => $cupo,
        ];
    }

    public static function consultarDisponibilidad(int $tarifaId): array
    {
        $tarifa = ProgramacionTramoPrecio::findOrFail($tarifaId);
        $programacion = Programacion::with([
            'viaje.tramos',
            'viaje.empresa',
            'autobus',
        ])->findOrFail($tarifa->programacion_id);
        $terminales = Terminal::obtenerSecuenciaRuta($programacion);
        Terminal::validarSalida($programacion, $tarifa->origen_terminal_id, $terminales);

        return self::disponibilidad($programacion, $tarifa, $terminales);
    }

    public static function disponibilidadPorTramos(Collection $programaciones): array
    {
        if ($programaciones->isEmpty()) {
            return [];
        }

        $programaciones->loadMissing([
            'viaje.tramos',
            'autobus',
            'tramoPrecios.origenTerminal',
            'tramoPrecios.destinoTerminal',
        ]);

        $reservas = Reserva::reservasQueBloqueanAsientos()
            ->whereIn('programacion_id', $programaciones->modelKeys())
            ->with('pasajes')
            ->get()
            ->groupBy('programacion_id');
        $resultado = [];

        foreach ($programaciones as $programacion) {
            $terminales = Terminal::obtenerSecuenciaRuta($programacion);
            $resultado[$programacion->id] = [];

            foreach ($programacion->tramoPrecios as $tarifa) {
                $resultado[$programacion->id][$tarifa->id] = self::disponibilidad(
                    $programacion,
                    $tarifa,
                    $terminales,
                    null,
                    $reservas->get($programacion->id, new Collection),
                ) + [
                    'origen' => $tarifa->origenTerminal?->nombre ?? '—',
                    'destino' => $tarifa->destinoTerminal?->nombre ?? '—',
                ];
            }
        }

        return $resultado;
    }
}
