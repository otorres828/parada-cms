<?php

namespace Database\Seeders;

use App\Models\Amenidad;
use App\Models\Autobus;
use App\Models\ConfiguracionCupon;
use App\Models\DatoBancario;
use App\Models\Empresa;
use App\Models\Estado;
use App\Models\Programacion;
use App\Models\ProgramacionTramoPrecio;
use App\Models\Reserva;
use App\Models\Terminal;
use App\Models\TipoCambio;
use App\Models\User;
use App\Models\UsuarioEmpresa;
use App\Models\Viaje;
use App\Models\ViajeTramo;
use App\Services\CuponService;
use App\Services\PagoReservaService;
use App\Services\ReservaService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Datos operativos para probar rutas, ocupación, pagos, cupones y órdenes de cobro.
 */
class AdminDemoSeeder extends Seeder
{
    private const TOTAL_ASIENTOS = 40;

    private const OCUPACIONES = [28, 30, 32, 34, 36, 38, 40, 29, 35, 39];

    private const NOMBRES = [
        'Ana',
        'Carlos',
        'Lucía',
        'José',
        'María',
        'Diego',
        'Elena',
        'Pedro',
        'Sofía',
        'Luis',
        'Valeria',
        'Andrés',
        'Camila',
        'Gabriel',
        'Daniela',
        'Miguel',
    ];

    private const APELLIDOS = [
        'González',
        'Rodríguez',
        'Pérez',
        'Martínez',
        'Hernández',
        'López',
        'García',
        'Ramírez',
        'Torres',
        'Flores',
        'Rojas',
        'Mendoza',
    ];

    public function run(): void
    {
        TipoCambio::query()->firstOrCreate([], [
            'valor_usd' => '855.66',
            'valor_eur' => '972.64',
            'valor' => '1.00',
            'timestamp' => now(),
        ]);

        $amenidades = $this->crearAmenidades();
        $terminales = $this->crearTerminales();
        
        $empresas = [
            ['nombre' => 'Expresos del Orinoco', 'rif' => 'J-40111222-1', 'email' => 'olivertorres1997@gmail.com@gmail.com'],
            ['nombre' => 'Líneas Andinas', 'rif' => 'J-40222333-2', 'email' => 'olivertorres1997+1@gmail.com@gmail.com'],
            ['nombre' => 'Transporte Costa Azul', 'rif' => 'J-40333444-3', 'email' => 'olivertorres1997+2@gmail.com@gmail.com'],
        ];

        foreach ($empresas as $empresaIndice => $datosEmpresa) {
            $empresa = $this->crearEmpresa($datosEmpresa, $empresaIndice);
            $datoBancario = $this->crearDatoBancario($empresa, $empresaIndice);
            $this->crearUsuariosEmpresa($empresa, $empresaIndice);
            $this->crearCampanaPrimeraCompra($empresa, $empresaIndice);
            $autobuses = $this->crearAutobuses($empresa, $empresaIndice, $amenidades);
            $viajes = $this->crearViajes($empresa, $empresaIndice, $terminales);
            $this->crearProgramacionesYReservas(
                $empresa,
                $empresaIndice,
                $datoBancario,
                $autobuses,
                $viajes,
                $terminales,
            );
        }

        $this->command?->info('Datos operativos de empresas, rutas, programaciones y ventas cargados correctamente.');
    }

    private function crearAmenidades()
    {
        return collect([
            'WiFi' => 'bi-wifi',
            'Aire acondicionado' => 'bi-snow',
            'Cargador USB' => 'bi-usb-plug',
            'Baño' => 'bi-door-open',
        ])->map(function ($icono, $nombre) {
            return Amenidad::updateOrCreate(
                ['nombre' => $nombre],
                ['icono' => $icono, 'estatus' => true],
            );
        });
    }

    private function crearTerminales(): array
    {
        $datos = [
            ['estado' => 'Distrito Capital', 'nombre' => 'Terminal La Bandera', 'direccion' => 'Avenida Nueva Granada, Caracas', 'latitud' => 10.4806, 'longitud' => -66.9036],
            ['estado' => 'Aragua', 'nombre' => 'Terminal Central de Maracay', 'direccion' => 'Avenida Constitución, Maracay', 'latitud' => 10.2469, 'longitud' => -67.5958],
            ['estado' => 'Carabobo', 'nombre' => 'Terminal Big Low Center', 'direccion' => 'Avenida Intercomunal, Valencia', 'latitud' => 10.1621, 'longitud' => -68.0077],
            ['estado' => 'Lara', 'nombre' => 'Terminal de Barquisimeto', 'direccion' => 'Avenida Florencio Jiménez, Barquisimeto', 'latitud' => 10.0678, 'longitud' => -69.3474],
            ['estado' => 'Zulia', 'nombre' => 'Terminal de Maracaibo', 'direccion' => 'Avenida Los Haticos, Maracaibo', 'latitud' => 10.6317, 'longitud' => -71.6406],
        ];

        $terminales = [];

        foreach ($datos as $terminal) {
            $estado = Estado::firstOrCreate(['nombre' => $terminal['estado']]);
            $terminales[] = Terminal::updateOrCreate(
                ['nombre' => $terminal['nombre']],
                [
                    'estado_id' => $estado->id,
                    'direccion' => $terminal['direccion'],
                    'latitud' => $terminal['latitud'],
                    'longitud' => $terminal['longitud'],
                    'estatus' => true,
                ],
            );
        }

        return $terminales;
    }

    private function crearEmpresa(array $datos, int $indice): Empresa
    {
        return Empresa::updateOrCreate(
            ['rif' => $datos['rif']],
            [
                'nombre' => $datos['nombre'],
                'telefono' => '0412-555-'.str_pad((string) ($indice + 1), 4, '0', STR_PAD_LEFT),
                'email' => $datos['email'],
                'tipo_contrato' => $indice === 1
                    ? Empresa::CONTRATO_NOSOTROS_RECIBIMOS
                    : Empresa::CONTRATO_ELLOS_RECIBEN,
                'dia_corte' => $indice === 1 ? null : 5,
                'dia_vencimiento' => $indice === 1 ? null : 7,
                'hora_corte' => '00:00:00',
                'hora_vencimiento' => '23:59:59',
                'bloqueada_por_cobranza_at' => null,
                'estatus' => true,
            ],
        );
    }

    private function crearDatoBancario(Empresa $empresa, int $indice): DatoBancario
    {
        return DatoBancario::updateOrCreate(
            [
                'empresa_id' => $empresa->id,
                'numero_cuenta_telefono' => '04125551'.str_pad((string) ($indice + 1), 3, '0', STR_PAD_LEFT),
            ],
            [
                'tipo' => DatoBancario::PAGO_MOVIL,
                'banco' => ['Banesco', 'Mercantil', 'Banco Nacional de Crédito'][$indice],
                'nombre_titular' => $empresa->nombre,
                'tipo_titular' => 'juridico',
                'numero_documento' => $empresa->rif,
                'tipo_cuenta' => null,
                'estatus' => DatoBancario::ACTIVO,
            ],
        );
    }

    private function crearUsuariosEmpresa(Empresa $empresa, int $empresaIndice): void
    {
        foreach (['Administrador de operaciones', 'Coordinador de ventas'] as $indice => $nombre) {
            UsuarioEmpresa::updateOrCreate(
                ['email' => 'usuario'.($empresaIndice + 1).($indice + 1).'@'.$this->dominioEmpresa($empresaIndice)],
                [
                    'empresa_id' => $empresa->id,
                    'nombre' => $nombre,
                    'password' => 'password',
                    'es_admin' => $indice === 0,
                    'estatus' => true,
                ],
            );
        }
    }

    private function crearCampanaPrimeraCompra(Empresa $empresa, int $empresaIndice): ConfiguracionCupon
    {
        return ConfiguracionCupon::updateOrCreate(
            ['codigo_personalizado' => 'PRIMERA-'.($empresaIndice + 1)],
            [
                'empresa_id' => $empresa->id,
                'nombre_campana' => 'Un dólar por pasaje en la primera compra',
                'tipo_cupon' => ConfiguracionCupon::TIPO_PERSONALIZADO,
                'modalidad' => ConfiguracionCupon::MODALIDAD_PRIMERA_COMPRA,
                'aplica_en' => ConfiguracionCupon::APLICA_EN_PASAJES,
                'cantidad_generar' => 500,
                'tipo_descuento' => 'monto_fijo',
                'monto_descuento' => '1.00',
                'fecha_inicio' => now()->copy()->subMonth()->startOfDay(),
                'fecha_fin' => now()->copy()->addMonth()->endOfDay(),
                'estatus' => 1,
            ],
        );
    }

    private function crearAutobuses(Empresa $empresa, int $empresaIndice, $amenidades): array
    {
        $autobuses = [];

        foreach ([1, 2] as $numero) {
            $autobus = Autobus::updateOrCreate(
                ['placa' => 'BUS-'.($empresaIndice + 1).'-'.$numero],
                [
                    'empresa_id' => $empresa->id,
                    'modelo' => $numero === 1 ? 'Marcopolo Paradiso G7' : 'Yutong ZK6122H9',
                    'tipo_asiento' => $numero === 1 ? 'Ejecutivo reclinable' : 'Semi cama',
                    'total_asientos' => self::TOTAL_ASIENTOS,
                    'es_plantilla' => false,
                    'estatus' => true,
                ],
            );

            $autobus->amenidades()->sync($amenidades->pluck('id')->all());
            $autobuses[] = $autobus;
        }

        return $autobuses;
    }

    private function crearViajes(Empresa $empresa, int $empresaIndice, array $terminales): array
    {
        $secuencias = [
            [0, 1, 2, 3, 4],
            [0, 1, 2],
            [1, 2, 3, 4],
            [4, 3, 2, 1, 0],
            [2, 3, 4],
        ];
        $viajes = [];

        foreach ($secuencias as $rutaIndice => $secuencia) {
            $viaje = Viaje::updateOrCreate(
                [
                    'empresa_id' => $empresa->id,
                    'origen_terminal_id' => $terminales[$secuencia[0]]->id,
                    'destino_terminal_id' => $terminales[$secuencia[array_key_last($secuencia)]]->id,
                ],
                [
                    'duracion_estimada' => sprintf('%02d:30:00', max(2, count($secuencia) * 2 - 1)),
                    'comentario' => $this->comentarioParadas($secuencia, $terminales),
                    'estatus' => true,
                ],
            );

            foreach (array_slice($secuencia, 0, -1) as $orden => $origenIndice) {
                $destinoIndice = $secuencia[$orden + 1];
                ViajeTramo::updateOrCreate(
                    ['viaje_id' => $viaje->id, 'orden' => $orden + 1],
                    [
                        'origen_terminal_id' => $terminales[$origenIndice]->id,
                        'destino_terminal_id' => $terminales[$destinoIndice]->id,
                        'duracion_estimada' => '01:30:00',
                    ],
                );
            }

            $viajes[] = ['modelo' => $viaje, 'secuencia' => $secuencia, 'indice' => $rutaIndice];
        }

        return $viajes;
    }

    private function crearProgramacionesYReservas(
        Empresa $empresa,
        int $empresaIndice,
        DatoBancario $datoBancario,
        array $autobuses,
        array $viajes,
        array $terminales,
    ): void {
        $hoy = today();

        foreach (range(0, 9) as $programacionIndice) {
            $fechaSalida = $hoy->copy()->subDays(9 - $programacionIndice);
            $ruta = $viajes[$programacionIndice % count($viajes)];
            $autobus = $autobuses[$programacionIndice % count($autobuses)];
            $programacion = Programacion::updateOrCreate(
                [
                    'viaje_id' => $ruta['modelo']->id,
                    'autobus_id' => $autobus->id,
                    'fecha_salida' => $fechaSalida->toDateString(),
                    'hora_salida' => '18:00:00',
                ],
                [
                    'asientos_totales' => self::TOTAL_ASIENTOS,
                    'estatus' => Programacion::ESTADO_PROGRAMADO,
                ],
            );

            $tarifa = $this->crearTarifas(
                $programacion,
                $ruta['secuencia'],
                $terminales,
                $empresaIndice,
                $programacionIndice,
            );

            $this->crearReservasPagadas(
                $empresa,
                $empresaIndice,
                $programacion,
                $programacionIndice,
                $tarifa,
                $datoBancario,
                self::OCUPACIONES[$programacionIndice],
                $fechaSalida,
            );

            if ($fechaSalida->isBefore($hoy)) {
                $programacion->update(['estatus' => Programacion::ESTADO_FINALIZADO]);
            }
        }
    }

    private function crearTarifas(
        Programacion $programacion,
        array $secuencia,
        array $terminales,
        int $empresaIndice,
        int $programacionIndice,
    ): ProgramacionTramoPrecio {
        $tarifaCompleta = null;
        $cantidadTerminales = count($secuencia);

        for ($origen = 0; $origen < $cantidadTerminales - 1; $origen++) {
            for ($destino = $origen + 1; $destino < $cantidadTerminales; $destino++) {
                $cantidadTramos = $destino - $origen;
                $precio = 9 + ($cantidadTramos * 7) + ($empresaIndice * 2) + $programacionIndice;
                $tarifa = ProgramacionTramoPrecio::updateOrCreate(
                    [
                        'programacion_id' => $programacion->id,
                        'origen_terminal_id' => $terminales[$secuencia[$origen]]->id,
                        'destino_terminal_id' => $terminales[$secuencia[$destino]]->id,
                    ],
                    [
                        'precio' => number_format($precio, 2, '.', ''),
                        'asientos_maximos_permitidos' => null,
                    ],
                );

                if ($origen === 0 && $destino === $cantidadTerminales - 1) {
                    $tarifaCompleta = $tarifa;
                }
            }
        }

        return $tarifaCompleta;
    }

    private function crearReservasPagadas(
        Empresa $empresa,
        int $empresaIndice,
        Programacion $programacion,
        int $programacionIndice,
        ProgramacionTramoPrecio $tarifa,
        DatoBancario $datoBancario,
        int $ocupacion,
        Carbon $fechaSalida,
    ): void {
        $esHistorica = $fechaSalida->isBefore(today());

        foreach (range(1, $ocupacion) as $asiento) {
            $secuenciaGlobal = ($empresaIndice * 1000) + ($programacionIndice * 100) + $asiento;
            $referencia = sprintf('RES-%02d-%02d-%03d', $empresaIndice + 1, $programacionIndice + 1, $asiento);
            $fechaCompra = $programacionIndice % 2 === 0
                ? $fechaSalida->copy()->subDay()->setTime(16, 0)
                : $fechaSalida->copy()->setTime(10, 0);
            $relojAnterior = Carbon::getTestNow();
            $existente = Reserva::where('codigo_referencia', $referencia)->first();

            if ($existente?->estado_pago === Reserva::ESTADO_PAGO_PENDIENTE) {
                try {
                    Carbon::setTestNow($fechaCompra);
                    $existente = PagoReservaService::confirmarPago($existente->id, $existente->monto_total);
                    $this->actualizarAbordaje($existente, $esHistorica, $asiento);
                } finally {
                    Carbon::setTestNow($relojAnterior);
                }

                continue;
            }

            if ($existente?->estado_pago === Reserva::ESTADO_PAGO_PAGADO) {
                $this->actualizarAbordaje($existente, $esHistorica, $asiento);

                continue;
            }

            if ($existente) {
                $cupon = $existente->cupon;
                $existente->delete();

                if ($cupon?->configuracionCupon?->tipo_cupon === ConfiguracionCupon::TIPO_PERSONALIZADO) {
                    $cupon->delete();
                }
            }

            try {
                Carbon::setTestNow($fechaCompra);
                DB::transaction(function () use ($empresaIndice, $programacionIndice, $asiento, $secuenciaGlobal, $referencia, $tarifa, $datoBancario, $fechaCompra, $esHistorica) {
                    $cliente = $this->crearCliente($empresaIndice, $programacionIndice, $asiento, $secuenciaGlobal);
                    $reserva = ReservaService::aplicarReserva($cliente, $tarifa->id);
                    $reserva->update(['codigo_referencia' => $referencia]);

                    ReservaService::agregarPasajero($cliente, $reserva->id, [
                        'nombre' => $cliente->name,
                        'apellido' => $cliente->lastname,
                        'tipo_documento' => 1,
                        'documento_identidad' => 'V-'.(10000000 + $secuenciaGlobal),
                        'fecha_nacimiento' => Carbon::create(1980 + ($asiento % 20), (($asiento - 1) % 12) + 1, (($asiento - 1) % 27) + 1)->toDateString(),
                        'tipo_pasajero' => 'adulto',
                    ]);

                    app(CuponService::class)->aplicarCupon(
                        $reserva->fresh(),
                        'PRIMERA-'.($empresaIndice + 1),
                    );
                    $reserva = ReservaService::prepararResumen($cliente, $reserva->id);
                    $reserva = PagoReservaService::pasarAPendiente(
                        $cliente,
                        $reserva->id,
                        $datoBancario->id,
                        sprintf('PAGO-%02d-%02d-%03d', $empresaIndice + 1, $programacionIndice + 1, $asiento),
                        $fechaCompra->toDateTimeString(),
                    );
                    $reserva = PagoReservaService::confirmarPago($reserva->id, $reserva->monto_total);
                    $this->actualizarAbordaje($reserva, $esHistorica, $asiento);
                }, 5);
            } finally {
                Carbon::setTestNow($relojAnterior);
            }
        }
    }

    private function actualizarAbordaje(Reserva $reserva, bool $esHistorica, int $asiento): void
    {
        if (! $esHistorica) {
            return;
        }

        $abordado = $asiento % 13 !== 0;
        $reserva->pasajes()->update([
            'abordado' => $abordado,
            'hora_abordaje' => $abordado ? '17:30:00' : null,
        ]);
    }

    private function crearCliente(int $empresaIndice, int $programacionIndice, int $asiento, int $secuencia): User
    {
        $nombre = self::NOMBRES[$secuencia % count(self::NOMBRES)];
        $apellido = self::APELLIDOS[$secuencia % count(self::APELLIDOS)];

        return User::updateOrCreate(
            ['email' => sprintf('cliente.%02d.%02d.%03d@pasajeros.test', $empresaIndice + 1, $programacionIndice + 1, $asiento)],
            [
                'name' => $nombre,
                'lastname' => $apellido,
                'telefono' => '0414'.str_pad((string) $secuencia, 7, '0', STR_PAD_LEFT),
                'date_birth' => Carbon::create(1980 + ($asiento % 20), 1, 1)->toDateString(),
                'sex' => $asiento % 2 === 0 ? '2' : '1',
                'password' => Str::random(40),
                'status' => User::ESTADO_ACTIVE,
            ],
        );
    }

    private function comentarioParadas(array $secuencia, array $terminales): string
    {
        return collect($secuencia)
            ->map(function ($terminalIndice, $orden) use ($terminales) {
                return ($orden + 1).'. '.$terminales[$terminalIndice]->nombre;
            })
            ->implode(PHP_EOL);
    }

    private function dominioEmpresa(int $indice): string
    {
        return ['expresosorinoco.test', 'lineasandinas.test', 'costazul.test'][$indice];
    }
}
