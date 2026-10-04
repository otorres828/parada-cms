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
            ['estado' => 'Bolívar', 'nombre' => 'San Félix "Monseñor Zabaleta"', 'direccion' => 'Avenida Centurión, San Félix, Ciudad Guayana', 'latitud' => 8.3591, 'longitud' => -62.6642],
            ['estado' => 'Bolívar', 'nombre' => 'Upata', 'direccion' => 'Calle 10, Upata', 'latitud' => 8.0037, 'longitud' => -62.3882],
            ['estado' => 'Bolívar', 'nombre' => 'El Callao', 'direccion' => 'Calle Heres, El Callao', 'latitud' => 7.3524, 'longitud' => -61.8155],
            ['estado' => 'Bolívar', 'nombre' => 'Tumeremo', 'direccion' => 'Troncal 10, Tumeremo', 'latitud' => 7.2990, 'longitud' => -61.5044],
            ['estado' => 'Bolívar', 'nombre' => 'Santa Elena de Uairén', 'direccion' => 'Troncal 10, Santa Elena de Uairén', 'latitud' => 4.6023, 'longitud' => -61.1103],
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
