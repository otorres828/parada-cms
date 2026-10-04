<?php

namespace Database\Seeders\Test;

use App\Models\Empresa;
use App\Models\Programacion;
use App\Models\Reserva;
use App\Models\TasaServicio;
use App\Models\TipoCambio;
use App\Support\PersonalData;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

class OperacionHistoricaDemoSeeder extends Seeder
{
    private const PASAJES_POR_AUTOBUS = 4;

    private const PASAJES_POR_CARRO = 2;

    private const TAMANO_LOTE_PROGRAMACIONES = 20;

    private array $secuencias = [
        'user' => 1,
        'viajero' => 1,
        'programacion' => 1,
        'tarifa' => 1,
        'reserva' => 1,
        'pasaje' => 1,
        'pago' => 1,
    ];

    public function run(): void
    {
        $desde = CarbonImmutable::parse((string) env('DEMO_FECHA_DESDE', '2026-01-01'))->startOfDay();
        $hasta = CarbonImmutable::parse((string) env('DEMO_FECHA_HASTA', CarbonImmutable::today()->toDateString()))->startOfDay();

        if (Programacion::searchAdmin('', ['date_from' => $desde->toDateString()])->exists()) {
            $this->command?->warn('La operación histórica ya fue generada. Se omite para evitar duplicados.');

            return;
        }

        DB::disableQueryLog();
        $this->inicializarSecuencias();

        $tipoCambioId = TipoCambio::vigente()?->id ?? TipoCambio::query()->value('id');
        $empresas = Empresa::query()
            ->with([
                'transportes' => fn ($query) => $query->where('estatus', 1)->orderBy('id'),
                'viajes' => fn ($query) => $query->where('estatus', 1)->with('tramos')->orderBy('id'),
                'datosBancarios' => fn ($query) => $query->where('estatus', 1)->orderBy('id'),
            ])
            ->where('estatus', Empresa::ESTADO_ACTIVE)
            ->orderBy('id')
            ->get();

        $buffers = $this->buffersVacios();
        $programacionesEnLote = 0;

        foreach ($empresas as $empresa) {
            $esAgencia = $empresa->tipo_entidad === Empresa::AGENCIA_AUTOBUS;
            $fecha = $desde;
            $indiceFecha = 0;

            while ($fecha->lessThanOrEqualTo($hasta)) {
                $horas = $esAgencia
                    ? ['06:00:00', '10:00:00', '14:00:00', '18:00:00']
                    : (in_array($fecha->dayOfWeekIso, [1, 3, 5], true) ? ['08:00:00'] : []);

                foreach ($horas as $turno => $hora) {
                    $viaje = $empresa->viajes[($indiceFecha + $turno) % $empresa->viajes->count()];
                    $transporte = $empresa->transportes[$turno % $empresa->transportes->count()];
                    $cantidadPasajes = $esAgencia ? self::PASAJES_POR_AUTOBUS : self::PASAJES_POR_CARRO;

                    $this->agregarProgramacion(
                        $buffers,
                        $empresa,
                        $viaje,
                        $transporte,
                        $empresa->datosBancarios->first()->id,
                        $tipoCambioId,
                        $fecha,
                        $hora,
                        $cantidadPasajes,
                        $turno,
                    );
                    $programacionesEnLote++;

                    if ($programacionesEnLote >= self::TAMANO_LOTE_PROGRAMACIONES) {
                        $this->insertarBuffers($buffers);
                        $buffers = $this->buffersVacios();
                        $programacionesEnLote = 0;
                    }
                }

                $fecha = $fecha->addDay();
                $indiceFecha++;
            }
        }

        $this->insertarBuffers($buffers);
        $this->command?->info(
            'Operación histórica generada desde '.$desde->format('d/m/Y').' hasta '.$hasta->format('d/m/Y').'.',
        );
    }

    private function agregarProgramacion(
        array &$buffers,
        Empresa $empresa,
        $viaje,
        $transporte,
        int $datoBancarioId,
        int $tipoCambioId,
        CarbonImmutable $fecha,
        string $hora,
        int $cantidadPasajes,
        int $turno,
    ): void {
        $ahora = now()->toDateTimeString();
        $programacionId = $this->siguiente('programacion');
        $esHistorica = $fecha->isBefore(CarbonImmutable::today());
        $buffers['programaciones'][] = [
            'id' => $programacionId,
            'viaje_id' => $viaje->id,
            'transporte_id' => $transporte->id,
            'asientos_totales' => $transporte->total_asientos,
            'estatus' => $esHistorica ? Programacion::ESTADO_FINALIZADO : Programacion::ESTADO_PROGRAMADO,
            'created_at' => $ahora,
            'updated_at' => $ahora,
        ];

        $terminales = collect([$viaje->origen_terminal_id])
            ->merge($viaje->tramosConsecutivos()->pluck('destino_terminal_id'))
            ->unique()
            ->values();
        // Un horario por terminal de esta programación; los O&D comparten sus extremos.
        $instante = $fecha->setTimeFromTimeString($hora);
        $horarios = [$viaje->origen_terminal_id => $instante];
        foreach ($viaje->tramosConsecutivos() as $tramo) {
            [$horas, $minutos, $segundos] = array_map('intval', explode(':', $tramo->duracion_estimada ?? '01:30:00'));
            $instante = $instante->addSeconds($horas * 3600 + $minutos * 60 + $segundos);
            $horarios[$tramo->destino_terminal_id] = $instante;
        }
        if ($viaje->tramos->isEmpty()) {
            [$horas, $minutos, $segundos] = array_map('intval', explode(':', $viaje->duracion_estimada));
            $horarios[$viaje->destino_terminal_id] = $instante->addSeconds($horas * 3600 + $minutos * 60 + $segundos);
        }
        $tarifaCompletaId = null;
        $precioCompleto = '0.00';

        for ($origen = 0; $origen < $terminales->count() - 1; $origen++) {
            for ($destino = $origen + 1; $destino < $terminales->count(); $destino++) {
                $tarifaId = $this->siguiente('tarifa');
                $precio = \App\Models\ViajeTramo::precioBase($viaje, $terminales[$origen], $terminales[$destino]);
                $buffers['tarifas'][] = [
                    'id' => $tarifaId,
                    'programacion_id' => $programacionId,
                    'origen_terminal_id' => $terminales[$origen],
                    'destino_terminal_id' => $terminales[$destino],
                    'fecha_salida' => $horarios[$terminales[$origen]]->toDateString(),
                    'hora_salida' => $horarios[$terminales[$origen]]->format('H:i:s'),
                    'fecha_llegada' => $horarios[$terminales[$destino]]->toDateString(),
                    'hora_llegada' => $horarios[$terminales[$destino]]->format('H:i:s'),
                    'precio' => $precio,
                    'asientos_maximos_permitidos' => null,
                    'created_at' => $ahora,
                    'updated_at' => $ahora,
                ];

                if ($origen === 0 && $destino === $terminales->count() - 1) {
                    $tarifaCompletaId = $tarifaId;
                    $precioCompleto = $precio;
                }
            }
        }

        $this->agregarVenta(
            $buffers,
            $empresa,
            $programacionId,
            $tarifaCompletaId,
            $terminales->first(),
            $terminales->last(),
            $datoBancarioId,
            $tipoCambioId,
            $fecha,
            $precioCompleto,
            $cantidadPasajes,
            $esHistorica,
        );
    }

    private function agregarVenta(
        array &$buffers,
        Empresa $empresa,
        int $programacionId,
        int $tarifaId,
        int $origenId,
        int $destinoId,
        int $datoBancarioId,
        int $tipoCambioId,
        CarbonImmutable $fechaSalida,
        string $precio,
        int $cantidadPasajes,
        bool $esHistorica,
    ): void {
        $usuarioId = $this->siguiente('user');
        $reservaId = $this->siguiente('reserva');
        $pagoId = $this->siguiente('pago');
        $fechaCompra = $fechaSalida->subDay()->setTime(16, 0);
        $tasaUnitaria = (float) (TasaServicio::query()->where('estatus', 1)->value('cantidad') ?? '1.25');
        $subtotal = round((float) $precio * $cantidadPasajes, 2);
        $tasaTotal = round($tasaUnitaria * $cantidadPasajes, 2);
        $total = round($subtotal + $tasaTotal, 2);
        $marcaTiempo = $fechaCompra->toDateTimeString();
        $codigo = ($empresa->tipo_entidad === Empresa::CONDUCTOR_CARRO ? 'CA-' : 'AU-')
            .strtoupper(str_pad(dechex($reservaId), 10, '0', STR_PAD_LEFT));

        $telefono = '0414'.str_pad((string) ($usuarioId % 10000000), 7, '0', STR_PAD_LEFT);
        $buffers['users'][] = [
            'id' => $usuarioId,
            'name' => 'Cliente '.str_pad((string) $usuarioId, 6, '0', STR_PAD_LEFT),
            'lastname' => 'Histórico',
            'email' => 'cliente'.$usuarioId.'@pasajeros.test',
            'telefono' => Crypt::encryptString($telefono),
            'telefono_hash' => PersonalData::hashTelefono($telefono),
            'date_birth' => '1990-01-01',
            'sex' => $usuarioId % 2 === 0 ? '2' : '1',
            'password' => null,
            'remember_token' => null,
            'status' => 1,
            'created_at' => $marcaTiempo,
            'updated_at' => $marcaTiempo,
        ];
        $buffers['reservas'][] = [
            'id' => $reservaId,
            'usuario_id' => $usuarioId,
            'programacion_id' => $programacionId,
            'origen_terminal_id' => $origenId,
            'destino_terminal_id' => $destinoId,
            'programacion_tramo_precio_id' => $tarifaId,
            'cupon_id' => null,
            'reprogramacion_id' => null,
            'tipos_cambios_id' => $tipoCambioId,
            'codigo_referencia' => $codigo,
            'monto_pasajes' => number_format($subtotal, 2, '.', ''),
            'descuento_aplicado' => '0.00',
            'exoneracion_tasa_json' => null,
            'tasa_servicio' => number_format($tasaTotal, 2, '.', ''),
            'monto_total' => number_format($total, 2, '.', ''),
            'estado_pago' => Reserva::ESTADO_PAGO_PAGADO,
            'fecha_compra' => $marcaTiempo,
            'fecha_pago' => $marcaTiempo,
            'fecha_expiracion' => null,
            'comentarios_auditoria' => null,
            'created_at' => $marcaTiempo,
            'updated_at' => $marcaTiempo,
        ];

        for ($asiento = 1; $asiento <= $cantidadPasajes; $asiento++) {
            $viajeroId = $this->siguiente('viajero');
            $pasajeId = $this->siguiente('pasaje');
            $documento = 'V-'.str_pad((string) $viajeroId, 8, '0', STR_PAD_LEFT);
            $viajeroSnapshot = [
                'version' => 1,
                'nombre' => 'Pasajero '.$asiento,
                'apellido' => 'Reserva '.$reservaId,
                'tipo_documento' => 1,
                'documento_identidad' => $documento,
                'fecha_nacimiento' => (1980 + ($asiento % 20)).'-01-01',
                'tipo_pasajero' => 'adulto',
            ];
            $servicioJson = json_encode([
                'monto_minimo' => (float) $precio <= 20 ? '0.00' : '20.01',
                'monto_maximo' => (float) $precio <= 20 ? '20.00' : null,
                'valor' => number_format($tasaUnitaria, 2, '.', ''),
                'tipo_servicio' => TasaServicio::MONTO_FIJO,
            ], JSON_UNESCAPED_UNICODE);
            $abordado = $esHistorica && $asiento % 13 !== 0;

            $buffers['viajeros'][] = [
                'id' => $viajeroId,
                'usuario_id' => $usuarioId,
                'nombre' => 'Pasajero '.$asiento,
                'apellido' => 'Reserva '.$reservaId,
                'tipo_documento' => 1,
                'documento_identidad' => Crypt::encryptString($documento),
                'documento_identidad_hash' => PersonalData::hashDocumento($documento),
                'fecha_nacimiento' => (1980 + ($asiento % 20)).'-01-01',
                'tipo_pasajero' => 'adulto',
                'estatus' => 1,
                'created_at' => $marcaTiempo,
                'updated_at' => $marcaTiempo,
            ];
            $buffers['pasajes'][] = [
                'id' => $pasajeId,
                'reserva_id' => $reservaId,
                'viajero_id' => $viajeroId,
                'viajero' => Crypt::encryptString(json_encode($viajeroSnapshot, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)),
                'viajero_documento_hash' => PersonalData::hashDocumento($documento),
                'numero_asiento' => $asiento,
                'precio_base' => $precio,
                'descuento' => '0.00',
                'subtotal' => $precio,
                'tasa_servicio' => number_format($tasaUnitaria, 2, '.', ''),
                'total' => number_format((float) $precio + $tasaUnitaria, 2, '.', ''),
                'servicio_json' => $servicioJson,
                'localizador' => strtoupper(str_pad(dechex($pasajeId), 20, '0', STR_PAD_LEFT)),
                'abordado' => $abordado,
                'hora_abordaje' => $abordado ? '05:30:00' : null,
                'created_at' => $marcaTiempo,
                'updated_at' => $marcaTiempo,
            ];
        }

        $buffers['pagos'][] = [
            'id' => $pagoId,
            'reserva_id' => $reservaId,
            'total' => number_format($total, 2, '.', ''),
            'tasa_servicio' => number_format($tasaTotal, 2, '.', ''),
            'metodo_pago' => $datoBancarioId,
            'referencia_pago' => 'PAGO-'.str_pad((string) $pagoId, 12, '0', STR_PAD_LEFT),
            'fecha_pago' => $marcaTiempo,
            'comprobante' => null,
            'created_at' => $marcaTiempo,
            'updated_at' => $marcaTiempo,
        ];
    }

    private function insertarBuffers(array $buffers): void
    {
        DB::transaction(function () use ($buffers) {
            foreach ([
                'programaciones' => 'programaciones',
                'tarifas' => 'programacion_tramo_precios',
                'users' => 'users',
                'viajeros' => 'viajeros',
                'reservas' => 'reservas',
                'pasajes' => 'pasajes',
                'pagos' => 'pagos_reservas',
            ] as $buffer => $tabla) {
                $tamano = DB::getDriverName() === 'sqlite' ? 20 : 500;

                foreach (array_chunk($buffers[$buffer], $tamano) as $filas) {
                    DB::table($tabla)->insert($filas);
                }
            }
        });
    }

    private function inicializarSecuencias(): void
    {
        foreach ([
            'user' => 'users',
            'viajero' => 'viajeros',
            'programacion' => 'programaciones',
            'tarifa' => 'programacion_tramo_precios',
            'reserva' => 'reservas',
            'pasaje' => 'pasajes',
            'pago' => 'pagos_reservas',
        ] as $secuencia => $tabla) {
            $this->secuencias[$secuencia] = ((int) DB::table($tabla)->max('id')) + 1;
        }
    }

    private function siguiente(string $secuencia): int
    {
        return $this->secuencias[$secuencia]++;
    }

    private function buffersVacios(): array
    {
        return [
            'programaciones' => [],
            'tarifas' => [],
            'users' => [],
            'viajeros' => [],
            'reservas' => [],
            'pasajes' => [],
            'pagos' => [],
        ];
    }
}
