<?php

namespace App\Models;

use App\Support\ConversorMoneda;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use App\Traits\TraitGeneral;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProgramacionTramoPrecio extends ModelHelper
{
    use TraitGeneral;

    protected $table = 'programacion_tramo_precios';

    protected $fillable = [
        'programacion_id',
        'origen_terminal_id',
        'destino_terminal_id',
        'fecha_salida',
        'hora_salida',
        'fecha_llegada',
        'hora_llegada',
        'precio',
        'asientos_maximos_permitidos',
    ];

    protected function casts(): array
    {
        return [
            'fecha_salida' => 'date',
            'fecha_llegada' => 'date',
            'precio' => 'decimal:2',
            'asientos_maximos_permitidos' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (self $tramo) {
            self::exigir(($tramo->fecha_salida === null) === ($tramo->hora_salida === null), 'hora_salida', 'Indica la fecha y la hora de salida del tramo.');
            self::exigir(($tramo->fecha_llegada === null) === ($tramo->hora_llegada === null), 'hora_llegada', 'Indica la fecha y la hora de llegada del tramo.');
            $salida = $tramo->getSalida();
            $llegada = $tramo->getLlegada();
            self::exigir($llegada === null || ($salida !== null && $llegada->greaterThan($salida)), 'hora_llegada', 'La llegada debe ser posterior a la salida del tramo.');
        });
    }

    public function getSalida(): ?Carbon
    {
        if ($this->fecha_salida !== null && $this->hora_salida !== null) {
            return $this->fecha_salida->copy()->setTimeFromTimeString($this->hora_salida);
        }

        return null;
    }

    public function getLlegada(): ?Carbon
    {
        return $this->fecha_llegada !== null && $this->hora_llegada !== null
            ? $this->fecha_llegada->copy()->setTimeFromTimeString($this->hora_llegada)
            : null;
    }

    public function validarSalida(bool $validarHora = true): void
    {
        $salida = $this->getSalida();
        self::exigir($salida !== null, 'tarifa', 'Configura la fecha y hora de salida de este tramo antes de vender.');
        self::exigir($this->getLlegada() !== null, 'tarifa', 'Configura la fecha y hora de llegada de este tramo antes de vender.');
        if ($validarHora) {
            self::exigir($salida->isFuture(), 'tarifa', 'La fecha y hora de salida de este tramo ya pasaron.');
        } else {
            self::exigir($salida->copy()->startOfDay()->greaterThanOrEqualTo(today()), 'tarifa', 'La fecha de salida de este tramo ya pasó.');
        }
    }

    public static function paraTaquilla(int $empresaId, string $fecha): Builder
    {
        return self::query()
            ->whereIn('programacion_id', Programacion::paraTaquilla($empresaId, soloFuturas: false)->reorder()->select('programaciones.id'))
            ->whereDate('fecha_salida', self::date($fecha))
            ->whereNotNull('hora_salida')
            ->with(['origenTerminal', 'destinoTerminal', 'programacion.viaje', 'programacion.transporte.amenidades'])
            ->orderBy('hora_salida')->orderBy('id');
    }

    public function programacion(): BelongsTo
    {
        return $this->belongsTo(Programacion::class, 'programacion_id');
    }

    public function origenTerminal(): BelongsTo
    {
        return $this->belongsTo(Terminal::class, 'origen_terminal_id');
    }

    public function destinoTerminal(): BelongsTo
    {
        return $this->belongsTo(Terminal::class, 'destino_terminal_id');
    }

    public function calcularMontoBs(?TipoCambio $tipoCambio = null): ?string
    {
        return ConversorMoneda::aBolivares($this->precio, $tipoCambio ?? TipoCambio::vigente());
    }
}
