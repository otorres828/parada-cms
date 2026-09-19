<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Amenidad;
use App\Models\Auditoria;
use App\Models\Autobus;
use App\Models\Configuracion;
use App\Models\ConfiguracionCupon;
use App\Models\Cupon;
use App\Models\Empresa;
use App\Models\Estado;
use App\Models\Movimiento;
use App\Models\Pago;
use App\Models\Pasaje;
use App\Models\PermissionAdmin;
use App\Models\Programacion;
use App\Models\Reembolso;
use App\Models\Reserva;
use App\Models\Retiro;
use App\Models\Terminal;
use App\Models\User;
use App\Models\UsuarioEmpresa;
use App\Models\Viaje;
use App\Models\Viajero;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/** Datos ficticios aditivos. Ejecutar explícitamente; nunca se llama desde DatabaseSeeder. */
class AdminDemoSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $actor = Admin::firstOrCreate(
                ['username' => 'demo-operador'],
                [
                    'name' => 'DEMO · Operador ficticio',
                    'email' => 'demo-operador@example.test',
                    'password' => Str::random(40),
                    'level' => 3,
                    'status' => 2,
                ],
            );
            $actor->permissions()->syncWithoutDetaching(
                PermissionAdmin::where('status', 1)
                    ->whereIn('url', ['list', 'detail'])
                    ->whereHas('section', fn($q) => $q->whereIn('url', ['empresas', 'reservas', 'pasajes', 'pagos', 'programaciones']))
                    ->pluck('id')
                    ->mapWithKeys(fn($id) => [$id => ['status' => 1]])
                    ->all(),
            );
            // Ninguna cuenta ficticia recibe una contraseña pública o acceso de dueño.
            $amenities = collect(['WiFi' => 'bi-wifi', 'Aire acondicionado' => 'bi-snow', 'USB' => 'bi-usb-plug', 'Baño' => 'bi-door-open'])->map(fn($icon, $label) => Amenidad::firstOrCreate(['nombre' => 'DEMO · ' . $label], ['icono' => $icon, 'estatus' => true]));
            $terminals = [];
            foreach (['Distrito Capital', 'Carabobo', 'Lara', 'Zulia'] as $i => $name) {
                $state = Estado::where('nombre', $name)->firstOrFail();
                $terminals[] = Terminal::firstOrCreate(
                    ['nombre' => 'DEMO · Terminal ' . $name, 'estado_id' => $state->id],
                    [
                        'direccion' => 'Dirección ficticia para pruebas del panel',
                        'latitud' => 10.5 + $i / 10,
                        'longitud' => -66.9 - $i / 10,
                        'estatus' => true,
                    ],
                );
            }
            $travelers = [];
            foreach (['Ana', 'Carlos', 'Lucía', 'José', 'María', 'Diego', 'Elena', 'Pedro', 'Sofía', 'Luis', 'Valeria', 'Andrés'] as $i => $name) {
                $user = User::firstOrCreate(
                    ['email' => 'demo-cliente-' . ($i + 1) . '@example.test'],
                    [
                        'name' => 'DEMO ' . $name,
                        'lastname' => 'Prueba',
                        'password' => Str::random(40),
                        'status' => 1,
                        'date_birth' => '1990-01-01',
                    ],
                );
                $travelers[] = Viajero::firstOrCreate(
                    ['usuario_id' => $user->id, 'documento_identidad' => 'DEMO-DOC-' . ($i + 1)],
                    [
                        'nombre' => $user->name,
                        'apellido' => 'Prueba',
                        'fecha_nacimiento' => '1990-01-01',
                        'tipo_pasajero' => 'adulto',
                    ],
                );
            }
            foreach (['Expreso del Norte', 'Rutas del Pacífico', 'Viajes del Altiplano'] as $c => $name) {
                $company = Empresa::firstOrCreate(
                    ['rif' => 'DEMO-EMP-' . ($c + 1)],
                    [
                        'nombre' => 'DEMO · ' . $name,
                        'telefono' => '0000-0000',
                        'email' => 'demo-empresa-' . ($c + 1) . '@example.test',
                        'estatus' => $c !== 2,
                        'retiros_habilitados' => $c !== 2,
                        'datos_bancarios' => 'DEMO: banco y cuenta ficticios. No realizar transferencias.',
                    ],
                );
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
                    ['codigo_base' => "DEMO-CAMP-$c"],
                    [
                        'empresa_id' => $company->id,
                        'nombre_campana' => 'DEMO · Bienvenida ' . $name,
                        'tipo_cupon' => 'unico',
                        'modalidad' => 'codigo',
                        'cantidad_generar' => 5,
                        'tipo_descuento' => 'fijo',
                        'monto_descuento' => '5.00',
                        'aplica_a' => 'pasajes',
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
                            'modelo' => 'DEMO · Autobús ejecutivo',
                            'tipo_asiento' => 'Reclinable',
                            'total_asientos' => 40,
                            'estatus' => true,
                        ],
                    );
                    $bus->amenidades()->syncWithoutDetaching($amenities->pluck('id')->all());
                    $trip = Viaje::firstOrCreate(['empresa_id' => $company->id, 'origen_terminal_id' => $terminals[$b]->id, 'destino_terminal_id' => $terminals[$b + 1]->id], ['duracion_estimada' => '03:30:00', 'estatus' => true]);
                    $comentarioParadas = implode("\n", [
                        'DEMO · Parada 1: Terminal Norte — 10 minutos.',
                        'DEMO · Parada 2: Terminal Central — 15 minutos.',
                        'DEMO · Parada 3: Terminal del Valle — 10 minutos.',
                        'DEMO · Parada 4: Terminal Las Palmas — 20 minutos.',
                        'DEMO · Parada 5: Terminal Sur — 10 minutos.',
                    ]);

                    if (blank($trip->comentario)) {
                        $trip->update(['comentario' => $comentarioParadas]);
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
                                'asientos_disponibles' => 36,
                                'precio_pasaje' => '40.00',
                                'estatus' => true,
                            ],
                        );

                        foreach (range(1, 4) as $r) {
                            $traveler = $travelers[($c * 4 + $r - 1) % count($travelers)];
                            $reference = "DEMO-RES-$c-$b-$day-$r";
                            $reservation = Reserva::firstOrCreate(
                                ['codigo_referencia' => $reference],
                                [
                                    'usuario_id' => $traveler->usuario_id,
                                    'programacion_id' => $departure->id,
                                    'monto_pasajes' => '40.00',
                                    'descuento_aplicado' => '0.00',
                                    'tasa_servicio' => '1.50',
                                    'monto_total' => '41.50',
                                    'estado_pago' => $r === 4 ? ($day < 0 ? Reserva::ESTADO_PAGO_CANCELADO : Reserva::ESTADO_PAGO_PENDIENTE) : Reserva::ESTADO_PAGO_PAGADO,
                                    'metodo_pago' => 'transferencia',
                                    'fecha_compra' => $anchor->copy()->subDays(abs($day))->addHours(10),
                                    'fecha_expiracion' => $r === 4 ? $anchor->copy()->addDay() : null,
                                ],
                            );
                            Pasaje::firstOrCreate(
                                ['codigo_qr_token' => "DEMO-QR-$c-$b-$day-$r"],
                                [
                                    'reserva_id' => $reservation->id,
                                    'viajero_id' => $traveler->id,
                                    'numero_asiento' => $r,
                                    'precio_base' => '40.00',
                                    'descuento' => '0.00',
                                    'precio_final' => '40.00',
                                    'tasa_servicio' => $reservation->tasa_servicio,
                                    'tipo_servicio' => 1,
                                    'valor_servicio' => $reservation->tasa_servicio,
                                    'base_tasa_servicio' => '40.00',
                                    'abordado' => $day < 0 && $r < 4,
                                    'fecha_abordaje' => $day < 0 && $r < 4 ? $departure->fecha_salida->copy()->addHours(8) : null,
                                ],
                            );
                            if ($r > 2) {
                                continue;
                            } // También existen ventas pagadas pendientes de conciliación.
                            $payment = Pago::firstOrCreate(
                                ['reserva_id' => $reservation->id],
                                [
                                    'empresa_id' => $company->id,
                                    'admin_id' => $actor->id,
                                    'monto' => $reservation->monto_total,
                                    'comision' => '0.00',
                                    'neto_empresa' => '40.00',
                                    'moneda' => 'USD',
                                    'referencia' => "DEMO-PAY-$c-$b-$day-$r",
                                    'metodo' => 'transferencia',
                                    'comentario' => 'DEMO: conciliación ficticia, sin cobro real.',
                                    'fecha_pago' => $reservation->fecha_compra,
                                ],
                            );
                            Movimiento::firstOrCreate(
                                ['clave' => 'pago:' . $payment->id],
                                [
                                    'empresa_id' => $company->id,
                                    'admin_id' => $actor->id,
                                    'pago_id' => $payment->id,
                                    'tipo' => 'venta',
                                    'monto' => $payment->neto_empresa,
                                    'moneda' => 'USD',
                                    'descripcion' => 'DEMO · Venta ficticia ' . $reference,
                                ],
                            );
                            $payments[] = $payment;
                        }
                    }
                }
                foreach (['pendiente', 'aprobado', 'rechazado'] as $i => $status) {
                    Retiro::firstOrCreate(
                        ['referencia' => "DEMO-RET-$c-$i"],
                        [
                            'empresa_id' => $company->id,
                            'admin_id' => $actor->id,
                            'monto' => '10.00',
                            'moneda' => 'USD',
                            'estatus' => $status,
                            'datos_bancarios' => 'DEMO: cuenta ficticia, no transferir.',
                            'comentario' => 'DEMO · Solicitud de prueba',
                            'revisado_por' => $i ? $actor->id : null,
                            'fecha_resolucion' => $i ? now() : null,
                        ],
                    );
                    Reembolso::firstOrCreate(
                        ['pago_id' => $payments[$i]->id],
                        [
                            'empresa_id' => $company->id,
                            'admin_id' => $actor->id,
                            'monto' => $payments[$i]->monto,
                            'moneda' => 'USD',
                            'estatus' => $status,
                            'motivo' => 'DEMO · Cambio de planes del pasajero ficticio',
                            'comentario' => 'DEMO · Revisión de prueba',
                            'revisado_por' => $i ? $actor->id : null,
                            'fecha_resolucion' => $i ? now() : null,
                        ],
                    );
                }
                Auditoria::firstOrCreate(
                    ['accion' => 'demo.cargado', 'entidad' => Empresa::class, 'entidad_id' => $company->id],
                    [
                        'admin_id' => $actor->id,
                        'datos' => ['origen' => 'AdminDemoSeeder', 'nota' => 'Datos ficticios, sin transacciones reales'],
                    ],
                );
            }
        });
        $this->command?->info('Datos DEMO cargados sin reemplazar registros existentes. Cuentas ficticias sin acceso público.');
    }
}
