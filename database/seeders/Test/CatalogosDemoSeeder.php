<?php

namespace Database\Seeders\Test;

use App\Models\Amenidad;
use App\Models\Estado;
use App\Models\Terminal;
use App\Models\TipoCambio;
use Illuminate\Database\Seeder;

class CatalogosDemoSeeder extends Seeder
{
    public function run(): void
    {
        TipoCambio::query()->firstOrCreate([], [
            'valor_usd' => '855.66',
            'valor_eur' => '972.64',
            'valor' => '1.00',
            'timestamp' => now(),
        ]);

        foreach ($this->amenidades() as $nombre => $icono) {
            Amenidad::updateOrCreate(
                ['nombre' => $nombre],
                ['icono' => $icono, 'estatus' => 1],
            );
        }

        foreach ($this->terminales() as $datos) {
            $estado = Estado::firstOrCreate(['nombre' => $datos['estado']]);

            Terminal::updateOrCreate(
                ['nombre' => $datos['nombre']],
                [
                    'estado_id' => $estado->id,
                    'direccion' => $datos['direccion'],
                    'latitud' => $datos['latitud'],
                    'longitud' => $datos['longitud'],
                    'estatus' => 1,
                ],
            );
        }
    }

    private function amenidades(): array
    {
        return [
            'WiFi' => 'bi-wifi',
            'Aire acondicionado' => 'bi-snow',
            'Cargador USB' => 'bi-usb-plug',
            'Baño' => 'bi-door-open',
            'Agua' => 'bi-droplet',
        ];
    }

    private function terminales(): array
    {
        return [
            ['estado' => 'Distrito Capital', 'nombre' => 'Terminal La Bandera', 'direccion' => 'Avenida Nueva Granada, Caracas', 'latitud' => 10.4806, 'longitud' => -66.9036],
            ['estado' => 'Aragua', 'nombre' => 'Terminal Central de Maracay', 'direccion' => 'Avenida Constitución, Maracay', 'latitud' => 10.2469, 'longitud' => -67.5958],
            ['estado' => 'Carabobo', 'nombre' => 'Terminal Big Low Center', 'direccion' => 'Avenida Intercomunal, Valencia', 'latitud' => 10.1621, 'longitud' => -68.0077],
            ['estado' => 'Lara', 'nombre' => 'Terminal de Barquisimeto', 'direccion' => 'Avenida Florencio Jiménez, Barquisimeto', 'latitud' => 10.0678, 'longitud' => -69.3474],
            ['estado' => 'Zulia', 'nombre' => 'Terminal de Maracaibo', 'direccion' => 'Avenida Los Haticos, Maracaibo', 'latitud' => 10.6317, 'longitud' => -71.6406],
        ];
    }
}
