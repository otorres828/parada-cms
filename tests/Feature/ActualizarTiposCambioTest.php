<?php

namespace Tests\Feature;

use App\Models\TipoCambio;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ActualizarTiposCambioTest extends TestCase
{
    use RefreshDatabase;

    public function test_el_comando_guarda_las_tasas_de_la_api(): void
    {
        Http::fake([
            config('services.bcv.endpoint') => Http::response([
                'USD' => 500.4606,
                'EUR' => 589.27233807,
                'date' => '2026-09-26',
            ]),
        ]);

        $this->artisan('tipos-cambio:actualizar')
            ->expectsOutputToContain('Tasa #1 registrada')
            ->assertSuccessful();

        $tipoCambio = TipoCambio::firstOrFail();

        $this->assertSame('500.46', $tipoCambio->valor_usd);
        $this->assertSame('589.27', $tipoCambio->valor_eur);
        $this->assertSame('1.00', $tipoCambio->valor);
        Http::assertSentCount(1);
    }

    public function test_el_comando_rechaza_una_respuesta_incompleta(): void
    {
        Http::fake([
            config('services.bcv.endpoint') => Http::response(['USD' => 500.4606]),
        ]);

        $this->artisan('tipos-cambio:actualizar')
            ->expectsOutputToContain('no contiene tasas USD y EUR válidas')
            ->assertFailed();

        $this->assertDatabaseCount('tipos_cambios', 0);
    }
}
