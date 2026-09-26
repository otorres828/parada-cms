<?php

namespace Tests\Unit;

use App\Models\Reserva;
use App\Models\TipoCambio;
use PHPUnit\Framework\TestCase;

class ReservaTipoCambioTest extends TestCase
{
    public function test_calcula_el_total_en_bolivares_con_la_tasa_historica(): void
    {
        $reserva = new Reserva(['monto_total' => '12.50']);
        $reserva->setRelation('tipoCambio', new TipoCambio(['valor_usd' => '500.12']));

        $this->assertSame('6251.50', $reserva->monto_total_bolivares);
    }
}
