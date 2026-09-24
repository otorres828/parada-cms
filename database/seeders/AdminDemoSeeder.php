<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Amenidad;
use App\Models\Auditoria;
use App\Models\Autobus;
use App\Models\ConfiguracionCupon;
use App\Models\Cupon;
use App\Models\DatoBancario;
use App\Models\Empresa;
use App\Models\Estado;
use App\Models\Programacion;
use App\Models\ProgramacionTramoPrecio;
use App\Models\Reembolso;
use App\Models\Reserva;
use App\Models\Terminal;
use App\Models\User;
use App\Models\UsuarioEmpresa;
use App\Models\Viaje;
use App\Models\ViajeTramo;
use App\Services\ReservaService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/** Datos ficticios aditivos con Matriz O&D multitramo. */
class AdminDemoSeeder extends Seeder
{
    public function run(): void
    {
        $actor = Admin::where('email', 'contador@pidetuparada.com')->firstOrFail();

        $amenities = collect(['WiFi' => 'bi-wifi', 'Aire acondicionado' => 'bi-snow', 'USB' => 'bi-usb-plug', 'Baño' => 'bi-door-open'])->map(fn ($icon, $label) => Amenidad::firstOrCreate(['nombre' => ''.$label], ['icono' => $icon, 'estatus' => true]));

        $terminals = [];
        foreach (['Distrito Capital', 'Aragua', 'Carabobo', 'Lara', 'Zulia'] as $i => $name) {
            $state = Estado::firstOrCreate(['nombre' => $name]);
            $terminals[] = Terminal::firstOrCreate(
                ['nombre' => 'Terminal '.$name],
                [
                    'estado_id' => $state->id,
                    'direccion' => 'Dirección ficticia para pruebas de panel',
                    'latitud' => 10.5 + $i / 10,
                    'longitud' => -66.9 - $i / 10,
                    'estatus' => true,
                ],
            );
        }

        $travelers = [];
        foreach (['Ana', 'Carlos', 'Lucía', 'José', 'María', 'Diego', 'Elena', 'Pedro', 'Sofía', 'Luis', 'Valeria', 'Andrés'] as $i => $name) {
            $user = User::firstOrCreate(
                ['email' => 'demo-cliente-'.($i + 1).'@example.test'],
                [
                    'name' => 'DEMO '.$name,
                    'lastname' => 'Prueba',
                    'password' => Str::random(40),
                    'status' => 1,
                    'date_birth' => '1990-01-01',
                ],
            );
            $travelers[] = [
                'cliente' => $user,
                'nombre' => $user->name,
                'apellido' => 'Prueba',
                'documento_identidad' => 'DEMO-DOC-'.($i + 1),
                'fecha_nacimiento' => '1990-01-01',
                'tipo_pasajero' => 'adulto',
            ];
        }

        foreach (['Expreso del Norte', 'Rutas del Pacífico', 'Viajes del Altiplano'] as $c => $name) {
            $company = Empresa::firstOrCreate(
                ['rif' => 'DEMO-EMP-'.($c + 1)],
                [
                    'nombre' => ''.$name,
                    'telefono' => '0000-0000',
                    'email' => 'demo-empresa-'.($c + 1).'@example.test',
                    'tipo_contrato' => $c === 1
                        ? Empresa::CONTRATO_NOSOTROS_RECIBIMOS
                        : Empresa::CONTRATO_ELLOS_RECIBEN,
                    'estatus' => true,
                ],
            );

            $datoBancario = DatoBancario::firstOrCreate(
                [
                    'empresa_id' => $company->id,
                    'numero_cuenta_telefono' => $c % 2 === 0
                        ? '0412000000'.($c + 1)
                        : '0134000000000000000'.($c + 1),
                ],
                [
                    'tipo' => $c % 2 === 0 ? DatoBancario::PAGO_MOVIL : DatoBancario::CUENTA_BANCARIA,
                    'banco' => $c % 2 === 0 ? 'Banesco' : 'Mercantil',
                    'nombre_titular' => $company->nombre,
                    'tipo_titular' => 'juridico',
                    'numero_documento' => $company->rif,
                    'tipo_cuenta' => $c % 2 === 0 ? null : 'corriente',
                    'estatus' => DatoBancario::ACTIVO,
                ],
            );

            $desactivarAlFinal = $company->wasRecentlyCreated && $c === 2;
            $anchor = $company->created_at->copy()->startOfDay();

            foreach ([1, 2] as $u) {
                UsuarioEmpresa::firstOrCreate(
                    ['email' => "demo-empleado-$c-$u@example.test"],
                    [
                        'empresa_id' => $company->id,
                        'nombre' => "DEMO · Colaborador $u",
                        'password' => Str::random(40),
                        'es_admin' => $u === 1,
                        'estatus' => false,
                    ],
                );
            }

            $campaign = ConfiguracionCupon::firstOrCreate(
                ['nombre_campana' => 'Bienvenida '.$name],
                [
                    'empresa_id' => $company->id,
                    'tipo_cupon' => ConfiguracionCupon::TIPO_RANDOM,
                    'modalidad' => ConfiguracionCupon::MODALIDAD_GENERAL,
                    'aplica_en' => $c % 2 === 0
                        ? ConfiguracionCupon::APLICA_EN_PASAJES
                        : ConfiguracionCupon::APLICA_EN_RESERVA,
                    'cantidad_generar' => 5,
                    'tipo_descuento' => 'monto_fijo',
                    'monto_descuento' => '5.00',
                    'fecha_inicio' => $anchor->copy()->subWeek(),
                    'fecha_fin' => $anchor->copy()->addMonth(),
                    'estatus' => true,
                ],
            );

            $coupons = [];
            foreach (range(1, 5) as $i) {
                $coupons[] = Cupon::firstOrCreate(['codigo' => "DEMO-CUP-$c-$i"], ['configuracion_cupon_id' => $campaign->id, 'redimido' => false]);
            }

            $payments = [];
            foreach ([0, 1] as $b) {
                $bus = Autobus::firstOrCreate(
                    ['placa' => "DEMO-BUS-$c-$b"],
                    [
                        'empresa_id' => $company->id,
                        'modelo' => 'Autobús ejecutivo',
                        'tipo_asiento' => 'Reclinable',
                        'total_asientos' => 40,
                        'estatus' => true,
                    ],
                );
                $bus->amenidades()->syncWithoutDetaching($amenities->pluck('id')->all());

                // Ruta física con 5 paradas (4 tramos consecutivos)
                $origenGlobal = $terminals[0];
                $destinoGlobal = $terminals[4];

                $trip = Viaje::firstOrCreate(
                    ['empresa_id' => $company->id, 'origen_terminal_id' => $origenGlobal->id, 'destino_terminal_id' => $destinoGlobal->id],
                    ['duracion_estimada' => '07:30:00', 'estatus' => true]
                );

                // Definir los 4 tramos físicos de la ruta
                $subTramosFisicos = [
                    ['origen' => $terminals[0], 'destino' => $terminals[1], 'orden' => 1, 'duracion' => '01:30:00'],
                    ['origen' => $terminals[1], 'destino' => $terminals[2], 'orden' => 2, 'duracion' => '01:15:00'],
                    ['origen' => $terminals[2], 'destino' => $terminals[3], 'orden' => 3, 'duracion' => '02:00:00'],
                    ['origen' => $terminals[3], 'destino' => $terminals[4], 'orden' => 4, 'duracion' => '02:45:00'],
                ];

                foreach ($subTramosFisicos as $sub) {
                    ViajeTramo::firstOrCreate(
                        ['viaje_id' => $trip->id, 'orden' => $sub['orden']],
                        [
                            'origen_terminal_id' => $sub['origen']->id,
                            'destino_terminal_id' => $sub['destino']->id,
                            'duracion_estimada' => $sub['duracion'],
                        ]
                    );
                }

                foreach ([-2, 1, 4] as $day) {
                    $departure = Programacion::firstOrCreate(
                        [
                            'viaje_id' => $trip->id,
                            'autobus_id' => $bus->id,
                            'fecha_salida' => $anchor->copy()->addDays($day),
                            'hora_salida' => '08:00:00',
                        ],
                        [
                            'asientos_totales' => 40,
                            'estatus' => true,
                        ],
                    );

                    // Crear Matriz O&D completa (Precios para todas las combinaciones Origen -> Destino)
                    $preciosOD = [
                        // Tramos cortos de 1 paso
                        [0, 1, '15.00', 10], // Distrito Capital -> Aragua
                        [1, 2, '15.00', 10], // Aragua -> Carabobo
                        [2, 3, '20.00', 10], // Carabobo -> Lara
                        [3, 4, '25.00', 10], // Lara -> Zulia
                        // Tramos medianos de 2 pasos
                        [0, 2, '25.00', null], // Distrito Capital -> Carabobo
                        [1, 3, '30.00', null], // Aragua -> Lara
                        [2, 4, '40.00', null], // Carabobo -> Zulia
                        // Tramos largos de 3 y 4 pasos
                        [0, 3, '45.00', null], // Distrito Capital -> Lara
                        [1, 4, '50.00', null], // Aragua -> Zulia
                        [0, 4, '60.00', null], // Distrito Capital -> Zulia (Viaje Completo Largo)
                    ];

                    $mapPrecios = [];
                    foreach ($preciosOD as $od) {
                        $ptp = ProgramacionTramoPrecio::firstOrCreate(
                            [
                                'programacion_id' => $departure->id,
                                'origen_terminal_id' => $terminals[$od[0]]->id,
                                'destino_terminal_id' => $terminals[$od[1]]->id,
                            ],
                            [
                                'precio' => $od[2],
                                'asientos_maximos_permitidos' => $od[3],
                            ]
                        );
                        $mapPrecios[$od[0].'-'.$od[1]] = $ptp;
                    }

                    // Generar Pasajes ficticios demostrando liberación y ocupación por tramos O&D
                    $casosPasajeros = [
                        // Pasajero 1: Asiento 12 | Tramo 1 (Distrito Capital -> Aragua)
                        ['idx' => 0, 'asiento' => 12, 'orig' => 0, 'dest' => 1, 'precio' => '15.00'],
                        // Pasajero 2: Asiento 12 | Tramo 2 (Aragua -> Carabobo) -> Mismo Asiento 12 reutilizado!
                        ['idx' => 1, 'asiento' => 12, 'orig' => 1, 'dest' => 2, 'precio' => '15.00'],
                        // Pasajero 3: Asiento 12 | Tramo 3 a 4 (Carabobo -> Zulia) -> Mismo Asiento 12 reutilizado por 3ra vez!
                        ['idx' => 2, 'asiento' => 12, 'orig' => 2, 'dest' => 4, 'precio' => '40.00'],
                        // Pasajero 4: Asiento 5 | Tramo Completo (Distrito Capital -> Zulia)
                        ['idx' => 3, 'asiento' => 5, 'orig' => 0, 'dest' => 4, 'precio' => '60.00'],
                    ];

                    foreach ($casosPasajeros as $r => $cp) {
                        $traveler = $travelers[($c * 4 + $cp['idx']) % count($travelers)];
                        $reference = "DEMO-RES-$c-$b-$day-".($r + 1);
                        $ptp = $mapPrecios[$cp['orig'].'-'.$cp['dest']] ?? null;

                        $reservation = Reserva::where('codigo_referencia', $reference)->first();

                        if (! $reservation) {
                            // No reactivar empresas existentes para insertar nuevas compras ficticias.
                            if (! $company->estatus) {
                                continue;
                            }

                            $salida = Carbon::parse($departure->fecha_salida->format('Y-m-d').' '.$departure->hora_salida);
                            $fechaCompra = now()->lt($salida) ? now() : $salida->copy()->subHour();
                            $relojAnterior = Carbon::getTestNow();

                            try {
                                // Simula el instante de compra de los viajes históricos.
                                Carbon::setTestNow($fechaCompra);

                                $reservation = DB::transaction(function () use ($reference, $traveler, $ptp, $cp, $r, $day, $b, $c, $coupons, $datoBancario) {
                                    $cliente = $traveler['cliente'];
                                    $reservation = ReservaService::aplicarReserva($cliente, $ptp->id);

                                    // Referencia estable del fixture para que el seeder sea repetible.
                                    $reservation->update(['codigo_referencia' => $reference]);

                                    $pasajero = $traveler;
                                    unset($pasajero['cliente']);
                                    $pasajero['numero_asiento'] = $cp['asiento'];
                                    ReservaService::registrarPasajeros($cliente, $reservation->id, [$pasajero]);

                                    // Una compra de ejemplo con cupón por empresa.
                                    if ($b === 0 && $day === 1 && $r === 0) {
                                        ReservaService::aplicarCupon($cliente, $reservation->id, $coupons[0]->codigo);
                                    }

                                    $reservation = ReservaService::prepararResumen($cliente, $reservation->id);

                                    if ($r === 3 && $day < 0) {
                                        return ReservaService::cancelarReserva($cliente, $reservation->id);
                                    }

                                    $reservation = ReservaService::pasarAPendiente(
                                        $cliente,
                                        $reservation->id,
                                        $datoBancario->id,
                                        "DEMO-PAY-$c-$b-$day-".($r + 1),
                                        $reservation->fecha_compra->toDateTimeString(),
                                    );

                                    if ($r < 3) {
                                        // Confirmación ficticia del fixture; no se ejecuta ningún cobro externo.
                                        $reservation = ReservaService::confirmarPago($reservation->id, $reservation->monto_total);
                                    }

                                    return $reservation;
                                });
                            } finally {
                                Carbon::setTestNow($relojAnterior);
                            }

                            if ($day < 0 && $r < 3) {
                                $reservation->pasajes()->update([
                                    'abordado' => true,
                                    'hora_abordaje' => $salida->format('H:i:s'),
                                ]);
                            }
                        }

                        if ($r > 2) {
                            continue;
                        }

                        $payments[] = $reservation->pago;
                    }
                }
            }

            foreach (['pendiente', 'aprobado', 'rechazado'] as $i => $status) {
                if (isset($payments[$i])) {
                    Reembolso::firstOrCreate(
                        ['pago_reserva_id' => $payments[$i]->id],
                        [
                            'empresa_id' => $company->id,
                            'admin_id' => $actor->id,
                            'monto' => $payments[$i]->total,
                            'moneda' => 'USD',
                            'estatus' => $status,
                            'motivo' => 'Cambio de planes del pasajero ficticio',
                            'comentario' => 'Revisión de prueba',
                            'revisado_por' => $i ? $actor->id : null,
                            'fecha_resolucion' => $i ? now() : null,
                        ],
                    );
                }
            }

            if ($desactivarAlFinal) {
                $company->update(['estatus' => false]);
            }

            Auditoria::firstOrCreate(
                ['accion' => 'demo.cargado', 'entidad' => Empresa::class, 'entidad_id' => $company->id],
                [
                    'admin_id' => $actor->id,
                    'datos' => ['origen' => 'AdminDemoSeeder', 'nota' => 'Datos ficticios O&D multitramo cargados'],
                ],
            );
        }

        $this->command?->info('Datos DEMO multitramo O&D cargados correctamente.');
    }
}
