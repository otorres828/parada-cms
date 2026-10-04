<?php

namespace Database\Seeders\Test;

use App\Models\Amenidad;
use App\Models\CategoriaPreguntaFrecuente;
use App\Models\Estado;
use App\Models\PreguntaFrecuente;
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

        $this->crearCentroAyuda();
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

            /*
            |--------------------------------------------------------------------------
            | AMAZONAS
            |--------------------------------------------------------------------------
            */

            [
                'estado' => 'Amazonas',
                'nombre' => 'Terminal de Pasajeros de Puerto Ayacucho',
                'direccion' => 'Puerto Ayacucho, municipio Atures',
                'latitud' => 5.6639,
                'longitud' => -67.6236,
            ],

            /*
            |--------------------------------------------------------------------------
            | ANZOÁTEGUI
            |--------------------------------------------------------------------------
            */

            [
                'estado' => 'Anzoátegui',
                'nombre' => 'Terminal de Pasajeros de Barcelona',
                'direccion' => 'Barcelona, municipio Simón Bolívar',
                'latitud' => 10.1362,
                'longitud' => -64.6865,
            ],
            [
                'estado' => 'Anzoátegui',
                'nombre' => 'Terminal de Pasajeros de Puerto La Cruz',
                'direccion' => 'Puerto La Cruz, municipio Sotillo',
                'latitud' => 10.2167,
                'longitud' => -64.6167,
            ],
            [
                'estado' => 'Anzoátegui',
                'nombre' => 'Terminal de Pasajeros de El Tigre',
                'direccion' => 'El Tigre, municipio Simón Rodríguez',
                'latitud' => 8.8875,
                'longitud' => -64.2454,
            ],
            [
                'estado' => 'Anzoátegui',
                'nombre' => 'Terminal de Pasajeros de Anaco',
                'direccion' => 'Anaco, municipio Anaco',
                'latitud' => 9.4292,
                'longitud' => -64.4643,
            ],
            [
                'estado' => 'Anzoátegui',
                'nombre' => 'Terminal de Pasajeros de Pariaguán',
                'direccion' => 'Pariaguán, municipio Francisco de Miranda',
                'latitud' => 8.8436,
                'longitud' => -64.7105,
            ],

            /*
            |--------------------------------------------------------------------------
            | APURE
            |--------------------------------------------------------------------------
            */

            [
                'estado' => 'Apure',
                'nombre' => 'Terminal de Pasajeros de San Fernando de Apure',
                'direccion' => 'San Fernando de Apure',
                'latitud' => 7.8878,
                'longitud' => -67.4724,
            ],
            [
                'estado' => 'Apure',
                'nombre' => 'Terminal de Pasajeros de Guasdualito',
                'direccion' => 'Guasdualito, municipio Páez',
                'latitud' => 7.2424,
                'longitud' => -70.7324,
            ],
            [
                'estado' => 'Apure',
                'nombre' => 'Terminal de Pasajeros de Elorza',
                'direccion' => 'Elorza, municipio Rómulo Gallegos',
                'latitud' => 7.0593,
                'longitud' => -69.4977,
            ],

            /*
            |--------------------------------------------------------------------------
            | ARAGUA
            |--------------------------------------------------------------------------
            */

            [
                'estado' => 'Aragua',
                'nombre' => 'Terminal Central de Maracay',
                'direccion' => 'Maracay, municipio Girardot',
                'latitud' => 10.2469,
                'longitud' => -67.5958,
            ],
            [
                'estado' => 'Aragua',
                'nombre' => 'Terminal de Pasajeros de La Victoria',
                'direccion' => 'La Victoria, municipio José Félix Ribas',
                'latitud' => 10.2272,
                'longitud' => -67.3337,
            ],
            [
                'estado' => 'Aragua',
                'nombre' => 'Terminal de Pasajeros de Cagua',
                'direccion' => 'Cagua, municipio Sucre',
                'latitud' => 10.1863,
                'longitud' => -67.4599,
            ],
            [
                'estado' => 'Aragua',
                'nombre' => 'Terminal de Pasajeros de Villa de Cura',
                'direccion' => 'Villa de Cura, municipio Zamora',
                'latitud' => 10.0386,
                'longitud' => -67.4894,
            ],
            [
                'estado' => 'Aragua',
                'nombre' => 'Terminal de Pasajeros de Turmero',
                'direccion' => 'Turmero, municipio Santiago Mariño',
                'latitud' => 10.2286,
                'longitud' => -67.4720,
            ],

            /*
            |--------------------------------------------------------------------------
            | BARINAS
            |--------------------------------------------------------------------------
            */

            [
                'estado' => 'Barinas',
                'nombre' => 'Terminal de Pasajeros Nuestra Señora del Pilar',
                'direccion' => 'Barinas, municipio Barinas',
                'latitud' => 8.6226,
                'longitud' => -70.2075,
            ],
            [
                'estado' => 'Barinas',
                'nombre' => 'Terminal de Pasajeros de Barinitas',
                'direccion' => 'Barinitas, municipio Bolívar',
                'latitud' => 8.7622,
                'longitud' => -70.4110,
            ],
            [
                'estado' => 'Barinas',
                'nombre' => 'Terminal de Pasajeros de Socopó',
                'direccion' => 'Socopó, municipio Antonio José de Sucre',
                'latitud' => 8.2301,
                'longitud' => -70.8211,
            ],
            [
                'estado' => 'Barinas',
                'nombre' => 'Terminal de Pasajeros de Santa Bárbara',
                'direccion' => 'Santa Bárbara de Barinas',
                'latitud' => 7.8128,
                'longitud' => -71.1775,
            ],

            /*
            |--------------------------------------------------------------------------
            | BOLÍVAR
            |--------------------------------------------------------------------------
            */

            [
                'estado' => 'Bolívar',
                'nombre' => 'Terminal de Pasajeros de Ciudad Bolívar',
                'direccion' => 'Ciudad Bolívar, municipio Angostura del Orinoco',
                'latitud' => 8.1292,
                'longitud' => -63.5409,
            ],
            [
                'estado' => 'Bolívar',
                'nombre' => 'San Félix "Monseñor Zabaleta"',
                'direccion' => 'Avenida Centurión, San Félix, Ciudad Guayana',
                'latitud' => 8.3591,
                'longitud' => -62.6642,
            ],
            [
                'estado' => 'Bolívar',
                'nombre' => 'Terminal de Pasajeros de Upata',
                'direccion' => 'Upata, municipio Piar',
                'latitud' => 8.0037,
                'longitud' => -62.3882,
            ],
            [
                'estado' => 'Bolívar',
                'nombre' => 'Terminal de Pasajeros de El Callao',
                'direccion' => 'El Callao',
                'latitud' => 7.3524,
                'longitud' => -61.8155,
            ],
            [
                'estado' => 'Bolívar',
                'nombre' => 'Terminal de Pasajeros de Tumeremo',
                'direccion' => 'Troncal 10, Tumeremo',
                'latitud' => 7.2990,
                'longitud' => -61.5044,
            ],
            [
                'estado' => 'Bolívar',
                'nombre' => 'Terminal de Pasajeros de Santa Elena de Uairén',
                'direccion' => 'Troncal 10, Santa Elena de Uairén',
                'latitud' => 4.6023,
                'longitud' => -61.1103,
            ],

            /*
            |--------------------------------------------------------------------------
            | CARABOBO
            |--------------------------------------------------------------------------
            */

            [
                'estado' => 'Carabobo',
                'nombre' => 'Terminal Big Low Center',
                'direccion' => 'San Diego, Gran Valencia',
                'latitud' => 10.2190,
                'longitud' => -67.9636,
            ],
            [
                'estado' => 'Carabobo',
                'nombre' => 'Terminal de Pasajeros de Puerto Cabello',
                'direccion' => 'Puerto Cabello',
                'latitud' => 10.4700,
                'longitud' => -68.0100,
            ],
            [
                'estado' => 'Carabobo',
                'nombre' => 'Terminal de Pasajeros de Guacara',
                'direccion' => 'Guacara',
                'latitud' => 10.2261,
                'longitud' => -67.8770,
            ],

            /*
            |--------------------------------------------------------------------------
            | COJEDES
            |--------------------------------------------------------------------------
            */

            [
                'estado' => 'Cojedes',
                'nombre' => 'Terminal de Pasajeros de San Carlos',
                'direccion' => 'San Carlos',
                'latitud' => 9.6612,
                'longitud' => -68.5827,
            ],
            [
                'estado' => 'Cojedes',
                'nombre' => 'Terminal de Pasajeros de Tinaquillo',
                'direccion' => 'Tinaquillo',
                'latitud' => 9.9186,
                'longitud' => -68.3047,
            ],

            /*
            |--------------------------------------------------------------------------
            | DELTA AMACURO
            |--------------------------------------------------------------------------
            */

            [
                'estado' => 'Delta Amacuro',
                'nombre' => 'Terminal de Pasajeros de Tucupita',
                'direccion' => 'Avenida Perimetral, Tucupita',
                'latitud' => 9.0573,
                'longitud' => -62.0508,
            ],

            /*
            |--------------------------------------------------------------------------
            | DISTRITO CAPITAL
            |--------------------------------------------------------------------------
            */

            [
                'estado' => 'Distrito Capital',
                'nombre' => 'Terminal La Bandera',
                'direccion' => 'La Bandera, Caracas',
                'latitud' => 10.4770,
                'longitud' => -66.9278,
            ],

            /*
            |--------------------------------------------------------------------------
            | FALCÓN
            |--------------------------------------------------------------------------
            */

            [
                'estado' => 'Falcón',
                'nombre' => 'Terminal Polica Salas de Coro',
                'direccion' => 'Santa Ana de Coro',
                'latitud' => 11.4045,
                'longitud' => -69.6734,
            ],
            [
                'estado' => 'Falcón',
                'nombre' => 'Terminal de Pasajeros de Punto Fijo',
                'direccion' => 'Punto Fijo, municipio Carirubana',
                'latitud' => 11.6956,
                'longitud' => -70.1996,
            ],
            [
                'estado' => 'Falcón',
                'nombre' => 'Terminal de Pasajeros de Tucacas',
                'direccion' => 'Tucacas, municipio Silva',
                'latitud' => 10.7907,
                'longitud' => -68.3259,
            ],
            [
                'estado' => 'Falcón',
                'nombre' => 'Terminal de Pasajeros de Chichiriviche',
                'direccion' => 'Chichiriviche, municipio Monseñor Iturriza',
                'latitud' => 10.9288,
                'longitud' => -68.2760,
            ],

            /*
            |--------------------------------------------------------------------------
            | GUÁRICO
            |--------------------------------------------------------------------------
            */

            [
                'estado' => 'Guárico',
                'nombre' => 'Terminal de Pasajeros de San Juan de los Morros',
                'direccion' => 'San Juan de los Morros',
                'latitud' => 9.9115,
                'longitud' => -67.3538,
            ],
            [
                'estado' => 'Guárico',
                'nombre' => 'Terminal de Pasajeros de Calabozo',
                'direccion' => 'Calabozo',
                'latitud' => 8.9242,
                'longitud' => -67.4293,
            ],
            [
                'estado' => 'Guárico',
                'nombre' => 'Terminal de Pasajeros de Valle de la Pascua',
                'direccion' => 'Valle de la Pascua',
                'latitud' => 9.2155,
                'longitud' => -66.0073,
            ],
            [
                'estado' => 'Guárico',
                'nombre' => 'Terminal de Pasajeros de Zaraza',
                'direccion' => 'Zaraza',
                'latitud' => 9.3503,
                'longitud' => -65.3245,
            ],
            [
                'estado' => 'Guárico',
                'nombre' => 'Terminal de Pasajeros de Altagracia de Orituco',
                'direccion' => 'Altagracia de Orituco',
                'latitud' => 9.8601,
                'longitud' => -66.3814,
            ],

            /*
            |--------------------------------------------------------------------------
            | LA GUAIRA
            |--------------------------------------------------------------------------
            */

            [
                'estado' => 'La Guaira',
                'nombre' => 'Terminal de Pasajeros de La Guaira',
                'direccion' => 'La Guaira',
                'latitud' => 10.5990,
                'longitud' => -66.9346,
            ],
            [
                'estado' => 'La Guaira',
                'nombre' => 'Terminal de Pasajeros de Catia La Mar',
                'direccion' => 'Catia La Mar',
                'latitud' => 10.6038,
                'longitud' => -67.0303,
            ],

            /*
            |--------------------------------------------------------------------------
            | LARA
            |--------------------------------------------------------------------------
            */

            [
                'estado' => 'Lara',
                'nombre' => 'Terminal de Pasajeros de Barquisimeto',
                'direccion' => 'Barquisimeto, municipio Iribarren',
                'latitud' => 10.0647,
                'longitud' => -69.3570,
            ],
            [
                'estado' => 'Lara',
                'nombre' => 'Terminal de Pasajeros de Carora',
                'direccion' => 'Carora, municipio Torres',
                'latitud' => 10.1728,
                'longitud' => -70.0806,
            ],
            [
                'estado' => 'Lara',
                'nombre' => 'Terminal de Pasajeros de Quíbor',
                'direccion' => 'Quíbor, municipio Jiménez',
                'latitud' => 9.9287,
                'longitud' => -69.6201,
            ],
            [
                'estado' => 'Lara',
                'nombre' => 'Terminal de Pasajeros de El Tocuyo',
                'direccion' => 'El Tocuyo, municipio Morán',
                'latitud' => 9.7871,
                'longitud' => -69.7937,
            ],

            /*
            |--------------------------------------------------------------------------
            | MÉRIDA
            |--------------------------------------------------------------------------
            */

            [
                'estado' => 'Mérida',
                'nombre' => 'Terminal de Pasajeros José Antonio Paredes',
                'direccion' => 'Mérida, municipio Libertador',
                'latitud' => 8.5897,
                'longitud' => -71.1561,
            ],
            [
                'estado' => 'Mérida',
                'nombre' => 'Terminal de Pasajeros Abelardo Pernía',
                'direccion' => 'El Vigía, municipio Alberto Adriani',
                'latitud' => 8.6216,
                'longitud' => -71.6502,
            ],
            [
                'estado' => 'Mérida',
                'nombre' => 'Terminal de Pasajeros de Tovar',
                'direccion' => 'Tovar',
                'latitud' => 8.3308,
                'longitud' => -71.7522,
            ],
            [
                'estado' => 'Mérida',
                'nombre' => 'Terminal de Pasajeros de Bailadores',
                'direccion' => 'Bailadores',
                'latitud' => 8.2539,
                'longitud' => -71.8280,
            ],

            /*
            |--------------------------------------------------------------------------
            | MIRANDA
            |--------------------------------------------------------------------------
            */

            [
                'estado' => 'Miranda',
                'nombre' => 'Terminal de Oriente Antonio José de Sucre',
                'direccion' => 'Carretera Petare - Guarenas, municipio Sucre',
                'latitud' => 10.5001,
                'longitud' => -66.7878,
            ],
            [
                'estado' => 'Miranda',
                'nombre' => 'Terminal de Pasajeros de Los Teques',
                'direccion' => 'Los Teques',
                'latitud' => 10.3410,
                'longitud' => -67.0404,
            ],
            [
                'estado' => 'Miranda',
                'nombre' => 'Terminal de Pasajeros de Guarenas',
                'direccion' => 'Guarenas',
                'latitud' => 10.4661,
                'longitud' => -66.6163,
            ],
            [
                'estado' => 'Miranda',
                'nombre' => 'Terminal de Pasajeros de Guatire',
                'direccion' => 'Guatire',
                'latitud' => 10.4727,
                'longitud' => -66.5422,
            ],
            [
                'estado' => 'Miranda',
                'nombre' => 'Terminal de Pasajeros de Charallave',
                'direccion' => 'Charallave',
                'latitud' => 10.2425,
                'longitud' => -66.8572,
            ],
            [
                'estado' => 'Miranda',
                'nombre' => 'Terminal de Pasajeros de Ocumare del Tuy',
                'direccion' => 'Ocumare del Tuy',
                'latitud' => 10.1184,
                'longitud' => -66.7753,
            ],
            [
                'estado' => 'Miranda',
                'nombre' => 'Terminal de Pasajeros de Higuerote',
                'direccion' => 'Higuerote',
                'latitud' => 10.4820,
                'longitud' => -66.1000,
            ],

            /*
            |--------------------------------------------------------------------------
            | MONAGAS
            |--------------------------------------------------------------------------
            */

            [
                'estado' => 'Monagas',
                'nombre' => 'Terminal Interurbano de Maturín',
                'direccion' => 'Maturín',
                'latitud' => 9.7457,
                'longitud' => -63.1832,
            ],
            [
                'estado' => 'Monagas',
                'nombre' => 'Terminal de Pasajeros de Punta de Mata',
                'direccion' => 'Punta de Mata',
                'latitud' => 9.6913,
                'longitud' => -63.6092,
            ],
            [
                'estado' => 'Monagas',
                'nombre' => 'Terminal de Pasajeros de Temblador',
                'direccion' => 'Temblador',
                'latitud' => 9.0053,
                'longitud' => -62.6446,
            ],
            [
                'estado' => 'Monagas',
                'nombre' => 'Terminal de Pasajeros de Caripito',
                'direccion' => 'Caripito',
                'latitud' => 10.1114,
                'longitud' => -63.0999,
            ],

            /*
            |--------------------------------------------------------------------------
            | NUEVA ESPARTA
            |--------------------------------------------------------------------------
            */

            [
                'estado' => 'Nueva Esparta',
                'nombre' => 'Terminal de Pasajeros de Porlamar',
                'direccion' => 'Porlamar, Isla de Margarita',
                'latitud' => 10.9577,
                'longitud' => -63.8697,
            ],
            [
                'estado' => 'Nueva Esparta',
                'nombre' => 'Terminal de Pasajeros de Juan Griego',
                'direccion' => 'Juan Griego, Isla de Margarita',
                'latitud' => 11.0816,
                'longitud' => -63.9665,
            ],

            /*
            |--------------------------------------------------------------------------
            | PORTUGUESA
            |--------------------------------------------------------------------------
            */

            [
                'estado' => 'Portuguesa',
                'nombre' => 'Terminal de Pasajeros de Acarigua',
                'direccion' => 'Acarigua',
                'latitud' => 9.5597,
                'longitud' => -69.2019,
            ],
            [
                'estado' => 'Portuguesa',
                'nombre' => 'Terminal de Pasajeros de Guanare',
                'direccion' => 'Guanare',
                'latitud' => 9.0436,
                'longitud' => -69.7489,
            ],
            [
                'estado' => 'Portuguesa',
                'nombre' => 'Terminal de Pasajeros de Turén',
                'direccion' => 'Turén',
                'latitud' => 9.2635,
                'longitud' => -69.1200,
            ],

            /*
            |--------------------------------------------------------------------------
            | SUCRE
            |--------------------------------------------------------------------------
            */

            [
                'estado' => 'Sucre',
                'nombre' => 'Terminal de Pasajeros de Cumaná',
                'direccion' => 'Cumaná',
                'latitud' => 10.4564,
                'longitud' => -64.1677,
            ],
            [
                'estado' => 'Sucre',
                'nombre' => 'Terminal de Pasajeros de Carúpano',
                'direccion' => 'Carúpano',
                'latitud' => 10.6678,
                'longitud' => -63.2585,
            ],
            [
                'estado' => 'Sucre',
                'nombre' => 'Terminal de Pasajeros de Güiria',
                'direccion' => 'Güiria, municipio Valdez',
                'latitud' => 10.5770,
                'longitud' => -62.2984,
            ],

            /*
            |--------------------------------------------------------------------------
            | TÁCHIRA
            |--------------------------------------------------------------------------
            */

            [
                'estado' => 'Táchira',
                'nombre' => 'Terminal de Pasajeros Genaro Méndez',
                'direccion' => 'San Cristóbal',
                'latitud' => 7.7669,
                'longitud' => -72.2252,
            ],
            [
                'estado' => 'Táchira',
                'nombre' => 'Terminal de Pasajeros de San Antonio del Táchira',
                'direccion' => 'San Antonio del Táchira',
                'latitud' => 7.8145,
                'longitud' => -72.4428,
            ],
            [
                'estado' => 'Táchira',
                'nombre' => 'Terminal de Pasajeros de La Fría',
                'direccion' => 'La Fría',
                'latitud' => 8.2152,
                'longitud' => -72.2482,
            ],
            [
                'estado' => 'Táchira',
                'nombre' => 'Terminal de Pasajeros de Rubio',
                'direccion' => 'Rubio',
                'latitud' => 7.7013,
                'longitud' => -72.3557,
            ],

            /*
            |--------------------------------------------------------------------------
            | TRUJILLO
            |--------------------------------------------------------------------------
            */

            [
                'estado' => 'Trujillo',
                'nombre' => 'Terminal de Pasajeros de Valera',
                'direccion' => 'Valera',
                'latitud' => 9.3178,
                'longitud' => -70.6036,
            ],
            [
                'estado' => 'Trujillo',
                'nombre' => 'Terminal de Pasajeros de Trujillo',
                'direccion' => 'Trujillo',
                'latitud' => 9.3658,
                'longitud' => -70.4347,
            ],
            [
                'estado' => 'Trujillo',
                'nombre' => 'Terminal de Pasajeros de Boconó',
                'direccion' => 'Boconó',
                'latitud' => 9.2467,
                'longitud' => -70.2614,
            ],
            [
                'estado' => 'Trujillo',
                'nombre' => 'Terminal de Pasajeros de Sabana de Mendoza',
                'direccion' => 'Sabana de Mendoza',
                'latitud' => 9.4348,
                'longitud' => -70.7702,
            ],

            /*
            |--------------------------------------------------------------------------
            | YARACUY
            |--------------------------------------------------------------------------
            */

            [
                'estado' => 'Yaracuy',
                'nombre' => 'Terminal Independencia de San Felipe',
                'direccion' => 'San Felipe',
                'latitud' => 10.3399,
                'longitud' => -68.7425,
            ],
            [
                'estado' => 'Yaracuy',
                'nombre' => 'Terminal de Pasajeros de Yaritagua',
                'direccion' => 'Yaritagua',
                'latitud' => 10.0807,
                'longitud' => -69.1261,
            ],
            [
                'estado' => 'Yaracuy',
                'nombre' => 'Terminal de Pasajeros de Chivacoa',
                'direccion' => 'Chivacoa',
                'latitud' => 10.1601,
                'longitud' => -68.8950,
            ],
            [
                'estado' => 'Yaracuy',
                'nombre' => 'Terminal de Pasajeros de Nirgua',
                'direccion' => 'Nirgua',
                'latitud' => 10.1508,
                'longitud' => -68.5642,
            ],

            /*
            |--------------------------------------------------------------------------
            | ZULIA
            |--------------------------------------------------------------------------
            */

            [
                'estado' => 'Zulia',
                'nombre' => 'Terminal de Pasajeros de Maracaibo',
                'direccion' => 'Maracaibo',
                'latitud' => 10.6427,
                'longitud' => -71.6125,
            ],
            [
                'estado' => 'Zulia',
                'nombre' => 'Terminal de Pasajeros de Cabimas',
                'direccion' => 'Cabimas',
                'latitud' => 10.3896,
                'longitud' => -71.4697,
            ],
            [
                'estado' => 'Zulia',
                'nombre' => 'Terminal de Pasajeros de Ciudad Ojeda',
                'direccion' => 'Ciudad Ojeda',
                'latitud' => 10.2000,
                'longitud' => -71.3280,
            ],
            [
                'estado' => 'Zulia',
                'nombre' => 'Terminal de Pasajeros de Machiques',
                'direccion' => 'Machiques de Perijá',
                'latitud' => 10.0644,
                'longitud' => -72.5450,
            ],
            [
                'estado' => 'Zulia',
                'nombre' => 'Terminal de Pasajeros de Santa Bárbara del Zulia',
                'direccion' => 'Santa Bárbara del Zulia',
                'latitud' => 8.9974,
                'longitud' => -71.9117,
            ],
        ];
    }

    private function crearCentroAyuda(): void
    {
        foreach ($this->categoriasCentroAyuda() as $ordenCategoria => $datosCategoria) {
            $preguntas = $datosCategoria['preguntas'];
            unset($datosCategoria['preguntas']);

            $categoria = CategoriaPreguntaFrecuente::updateOrCreate(
                ['slug' => $datosCategoria['slug']],
                $datosCategoria + [
                    'orden' => $ordenCategoria + 1,
                    'estatus' => CategoriaPreguntaFrecuente::ESTADO_ACTIVE,
                ],
            );

            foreach ($preguntas as $ordenPregunta => $datosPregunta) {
                PreguntaFrecuente::updateOrCreate(
                    ['slug' => $datosPregunta['slug']],
                    $datosPregunta + [
                        'categoria_pregunta_frecuente_id' => $categoria->id,
                        'destacada' => $ordenPregunta === 0,
                        'orden' => $ordenPregunta + 1,
                        'estatus' => PreguntaFrecuente::ESTADO_ACTIVE,
                    ],
                );
            }
        }
    }

    private function categoriasCentroAyuda(): array
    {
        return [
            [
                'nombre' => 'Pagos',
                'slug' => 'pagos',
                'descripcion' => 'Métodos de pago, transferencias y validación de comprobantes.',
                'icono' => 'bi-credit-card',
                'imagen' => null,
                'destacada' => true,
                'preguntas' => [
                    [
                        'pregunta' => '¿Cómo registrar correctamente una transferencia?',
                        'slug' => 'como-registrar-correctamente-una-transferencia',
                        'resumen' => 'Conoce los datos requeridos y cómo enviar la referencia de tu pago.',
                        'respuesta' => '<p>Selecciona la cuenta bancaria indicada por la empresa, realiza la transferencia por el monto exacto y registra la referencia solicitada. La reserva quedará pendiente mientras se valida el pago.</p>',
                        'palabras_clave' => 'pago, transferencia, referencia, comprobante',
                    ],
                    [
                        'pregunta' => '¿Por qué mi pago aparece pendiente?',
                        'slug' => 'por-que-mi-pago-aparece-pendiente',
                        'resumen' => 'Consulta qué significa la validación pendiente de una reserva.',
                        'respuesta' => '<p>El estado pendiente indica que la transferencia fue reportada y todavía debe ser verificada por la empresa transportista.</p>',
                        'palabras_clave' => 'pago pendiente, validación, reserva',
                    ],
                ],
            ],
            [
                'nombre' => 'Reservas',
                'slug' => 'reservas',
                'descripcion' => 'Compra de pasajes, pasajeros y consulta de reservas.',
                'icono' => 'bi-ticket-perforated',
                'imagen' => null,
                'destacada' => true,
                'preguntas' => [
                    [
                        'pregunta' => '¿Cómo comprar un pasaje?',
                        'slug' => 'como-comprar-un-pasaje',
                        'resumen' => 'Revisa el proceso para seleccionar un viaje y registrar a los pasajeros.',
                        'respuesta' => '<p>Busca el origen, destino y fecha del viaje. Selecciona una salida disponible, inicia sesión, registra los pasajeros y completa el pago.</p>',
                        'palabras_clave' => 'comprar pasaje, reserva, pasajeros, viaje',
                    ],
                    [
                        'pregunta' => '¿Dónde encuentro mis pasajes?',
                        'slug' => 'donde-encuentro-mis-pasajes',
                        'resumen' => 'Ubica tus reservas y pases digitales desde tu cuenta.',
                        'respuesta' => '<p>Ingresa a tu cuenta y abre el historial de reservas. En cada reserva pagada encontrarás los pasajes digitales de sus viajeros.</p>',
                        'palabras_clave' => 'mis pasajes, historial, qr, reserva',
                    ],
                ],
            ],
            [
                'nombre' => 'Cuenta y perfil',
                'slug' => 'cuenta-y-perfil',
                'descripcion' => 'Registro, acceso y administración de datos personales.',
                'icono' => 'bi-person-circle',
                'imagen' => null,
                'destacada' => true,
                'preguntas' => [
                    [
                        'pregunta' => '¿Cómo crear una cuenta?',
                        'slug' => 'como-crear-una-cuenta',
                        'resumen' => 'Conoce los datos necesarios para registrarte y comprar pasajes.',
                        'respuesta' => '<p>Selecciona la opción de registro, completa tus datos personales y confirma tu correo electrónico siguiendo las instrucciones recibidas.</p>',
                        'palabras_clave' => 'registro, crear cuenta, correo',
                    ],
                ],
            ],
            [
                'nombre' => 'Requisitos de viaje',
                'slug' => 'requisitos-de-viaje',
                'descripcion' => 'Documentación, equipaje y recomendaciones para abordar.',
                'icono' => 'bi-info-circle',
                'imagen' => null,
                'destacada' => true,
                'preguntas' => [
                    [
                        'pregunta' => '¿Qué documento debo presentar al abordar?',
                        'slug' => 'que-documento-debo-presentar-al-abordar',
                        'resumen' => 'Consulta la documentación requerida para validar tu pasaje.',
                        'respuesta' => '<p>Presenta el documento de identidad registrado para el viajero y el código QR del pasaje. La empresa puede solicitar requisitos adicionales según la ruta.</p>',
                        'palabras_clave' => 'documento, cédula, pasaporte, abordar, qr',
                    ],
                ],
            ],
            [
                'nombre' => 'Cambios y reembolsos',
                'slug' => 'cambios-y-reembolsos',
                'descripcion' => 'Reprogramaciones, cancelaciones y solicitudes de devolución.',
                'icono' => 'bi-arrow-repeat',
                'imagen' => null,
                'destacada' => false,
                'preguntas' => [
                    [
                        'pregunta' => '¿Cómo solicitar un reembolso?',
                        'slug' => 'como-solicitar-un-reembolso',
                        'resumen' => 'Revisa las condiciones y el proceso aplicable a una devolución.',
                        'respuesta' => '<p>Consulta primero las políticas de la empresa transportista. La empresa que recibió el pago es responsable de revisar y gestionar la solicitud.</p>',
                        'palabras_clave' => 'reembolso, devolución, cancelación',
                    ],
                ],
            ],
        ];
    }
}
