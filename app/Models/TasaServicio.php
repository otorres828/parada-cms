<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;

class TasaServicio extends ModelHelper
{
    public const MONTO_FIJO = 1;

    public const PORCENTAJE = 2;

    protected $attributes = ['tipo_servicio' => 1];

    protected $table = 'tasas_servicio';

    protected $fillable = [
        'tipo_servicio',
        'monto_minimo',
        'monto_maximo',
        'cantidad',
        'estatus',
    ];

    protected function casts(): array
    {
        return ['tipo_servicio' => 'integer', 'monto_minimo' => 'decimal:2', 'monto_maximo' => 'decimal:2', 'cantidad' => 'decimal:2', 'estatus' => 'boolean'];
    }

    public static function searchAdmin(string $search = '', array $filters = []): Builder
    {
        $query = self::query();

        if ($search !== '') {
            $query->where(
                fn ($q) => $q
                    ->where('monto_minimo', 'like', '%' . $search . '%')
                    ->orWhere('monto_maximo', 'like', '%' . $search . '%')
                    ->orWhere('cantidad', 'like', '%' . $search . '%'),
            );
        }

        if (isset($filters['status']) && $filters['status'] !== '') {
            $query->where('estatus', $filters['status']);
        }

        return $query;
    }

    public static function paraPrecio(string $precio): self
    {
        $matches = self::searchAdmin('', ['status' => 1])
            ->where('monto_minimo', '<=', $precio)
            ->where(fn ($q) => $q->whereNull('monto_maximo')->orWhere('monto_maximo', '>=', $precio))
            ->get();

        if ($matches->count() !== 1) {
            throw ValidationException::withMessages(['tasa_servicio' => 'Debe existir exactamente una tasa activa para el precio del pasaje.']);
        }

        return $matches->first();
    }

    public function calcular(string $precioFinal): string
    {
        if (bccomp($precioFinal, '0', 2) < 0 || bccomp($this->cantidad, '0', 2) < 0) {
            throw ValidationException::withMessages(['cantidad' => 'Los importes no pueden ser negativos.']);
        }

        if ($this->tipo_servicio === self::MONTO_FIJO) {
            return bcadd($this->cantidad, '0', 2);
        }

        if ($this->tipo_servicio !== self::PORCENTAJE || bccomp($this->cantidad, '100', 2) > 0) {
            throw ValidationException::withMessages(['cantidad' => 'El porcentaje debe estar entre 0 y 100.']);
        }

        // Redondeo monetario a dos decimales por boleto, antes de sumar la reserva.
        return bcadd(bcdiv(bcmul($precioFinal, $this->cantidad, 4), '100', 6), '0.005', 2);
    }

    public function validarRango(): void
    {
        if (!$this->estatus) {
            return;
        }
        $query = self::searchAdmin('', ['status' => 1])->when($this->exists, fn ($q) => $q->whereKeyNot($this->id));

        if ($this->monto_maximo !== null) {
            $query->where('monto_minimo', '<=', $this->monto_maximo);
        }
        $query->where(fn ($q) => $q->whereNull('monto_maximo')->orWhere('monto_maximo', '>=', $this->monto_minimo));

        if ($query->exists()) {
            // throw ValidationException::withMessages(['monto_minimo' => 'El rango se cruza con otra tasa activa. Revisa los límites.']);
        }
    }
}
