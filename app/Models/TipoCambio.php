<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

class TipoCambio extends ModelHelper
{
    public const UPDATED_AT = null;

    public const CREATED_AT = 'timestamp';

    protected $table = 'tipos_cambios';

    protected $fillable = [
        'valor_usd',
        'valor_eur',
        'valor',
        'timestamp',
    ];

    protected function casts(): array
    {
        return [
            'valor_usd' => 'decimal:2',
            'valor_eur' => 'decimal:2',
            'valor' => 'decimal:2',
            'timestamp' => 'datetime',
        ];
    }

    public static function vigente(): ?self
    {
        return self::query()->latest('timestamp')->latest('id')->first();
    }

    public function reservas(): HasMany
    {
        return $this->hasMany(Reserva::class, 'tipos_cambios_id');
    }
}
