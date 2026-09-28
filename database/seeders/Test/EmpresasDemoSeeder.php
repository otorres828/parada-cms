<?php

namespace Database\Seeders\Test;

use App\Models\Amenidad;
use App\Models\DatoBancario;
use App\Models\Empresa;
use App\Models\Terminal;
use App\Models\Transporte;
use App\Models\UsuarioEmpresa;
use App\Models\Viaje;
use App\Models\ViajeTramo;
use Illuminate\Database\Seeder;

class EmpresasDemoSeeder extends Seeder
{
    public const TOTAL_AGENCIAS = 50;

    public const TOTAL_CONDUCTORES = 100;

    public function run(): void
    {
        $terminales = Terminal::query()->orderBy('id')->get();
        $amenidades = Amenidad::query()->where('estatus', 1)->get();

        $totalAgencias = (int) env('DEMO_TOTAL_AGENCIAS', self::TOTAL_AGENCIAS);
        $totalConductores = (int) env('DEMO_TOTAL_CONDUCTORES', self::TOTAL_CONDUCTORES);

        for ($indice = 1; $indice <= $totalAgencias; $indice++) {
            $empresa = $this->crearEmpresa($indice, Empresa::AGENCIA_AUTOBUS);
            $this->crearDatosRelacionados($empresa, $indice, $amenidades, $terminales);
        }

        for ($indice = 1; $indice <= $totalConductores; $indice++) {
            $numero = $totalAgencias + $indice;
            $empresa = $this->crearEmpresa($numero, Empresa::CONDUCTOR_CARRO, $totalAgencias);
            $this->crearDatosRelacionados($empresa, $numero, $amenidades, $terminales);
        }

        $this->command?->info($totalAgencias.' agencias y '.$totalConductores.' conductores creados con sus transportes y rutas.');
    }

    private function crearEmpresa(int $numero, string $tipoEntidad, int $totalAgencias = self::TOTAL_AGENCIAS): Empresa
    {
        $esAgencia = $tipoEntidad === Empresa::AGENCIA_AUTOBUS;
        $identificacion = $esAgencia
            ? 'J-'.str_pad((string) (40000000 + $numero), 8, '0', STR_PAD_LEFT).'-'.$numero % 10
            : 'V-'.str_pad((string) (20000000 + $numero), 8, '0', STR_PAD_LEFT);

        return Empresa::updateOrCreate(
            ['rif' => $identificacion],
            [
                'nombre' => $esAgencia
                    ? 'Agencia de Autobuses '.str_pad((string) $numero, 3, '0', STR_PAD_LEFT)
                    : 'Conductor '.str_pad((string) ($numero - $totalAgencias), 3, '0', STR_PAD_LEFT),
                'tipo_entidad' => $tipoEntidad,
                'telefono' => '0412'.str_pad((string) $numero, 7, '0', STR_PAD_LEFT),
                'email' => 'empresa'.str_pad((string) $numero, 3, '0', STR_PAD_LEFT).'@transportes.test',
                'tipo_contrato' => $numero % 2 === 0
                    ? Empresa::CONTRATO_NOSOTROS_RECIBIMOS
                    : Empresa::CONTRATO_ELLOS_RECIBEN,
                'dia_corte' => 7,
                'dia_vencimiento' => 5,
                'hora_corte' => '00:00:00',
                'hora_vencimiento' => '23:59:59',
                'bloqueada_por_cobranza_at' => null,
                'estatus' => Empresa::ESTADO_ACTIVE,
            ],
        );
    }

    private function crearDatosRelacionados(Empresa $empresa, int $numero, $amenidades, $terminales): void
    {
        $this->crearDatoBancario($empresa, $numero);
        $this->crearUsuario($empresa, $numero);
        $this->crearTransportes($empresa, $numero, $amenidades);
        $this->crearRutas($empresa, $numero, $terminales);
    }

    private function crearDatoBancario(Empresa $empresa, int $numero): void
    {
        DatoBancario::updateOrCreate(
            ['empresa_id' => $empresa->id, 'numero_cuenta_telefono' => '0414'.str_pad((string) $numero, 7, '0', STR_PAD_LEFT)],
            [
                'tipo' => DatoBancario::PAGO_MOVIL,
                'banco' => ['Banesco', 'Mercantil', 'Banco Nacional de Crédito'][$numero % 3],
                'nombre_titular' => $empresa->nombre,
                'tipo_titular' => $empresa->tipo_entidad === Empresa::AGENCIA_AUTOBUS ? 'juridico' : 'personal',
                'numero_documento' => $empresa->rif,
                'tipo_cuenta' => null,
                'estatus' => DatoBancario::ACTIVO,
            ],
        );
    }

    private function crearUsuario(Empresa $empresa, int $numero): void
    {
        UsuarioEmpresa::updateOrCreate(
            ['email' => 'operaciones'.str_pad((string) $numero, 3, '0', STR_PAD_LEFT).'@transportes.test'],
            [
                'empresa_id' => $empresa->id,
                'nombre' => 'Responsable de operaciones',
                'password' => 'password',
                'es_admin' => true,
                'estatus' => 1,
            ],
        );
    }

    private function crearTransportes(Empresa $empresa, int $numero, $amenidades): void
    {
        $esAgencia = $empresa->tipo_entidad === Empresa::AGENCIA_AUTOBUS;
        $cantidad = $esAgencia ? 4 : 1;
        $amenidadesPermitidas = $esAgencia
            ? $amenidades
            : $amenidades->whereIn('nombre', ['WiFi', 'Aire acondicionado', 'Cargador USB']);

        for ($unidad = 1; $unidad <= $cantidad; $unidad++) {
            $transporte = Transporte::updateOrCreate(
                ['placa' => ($esAgencia ? 'BUS-' : 'CAR-').str_pad((string) $numero, 3, '0', STR_PAD_LEFT).'-'.$unidad],
                [
                    'empresa_id' => $empresa->id,
                    'tipo_transporte' => $esAgencia ? Transporte::AUTOBUS : Transporte::CARRO,
                    'modelo' => $esAgencia
                        ? ['Marcopolo Paradiso G7', 'Yutong ZK6122H9'][$unidad % 2]
                        : ['Toyota Corolla', 'Chery Arrizo 5', 'Toyota Yaris'][$numero % 3],
                    'tipo_asiento' => $esAgencia ? 'Ejecutivo reclinable' : 'Asiento estándar',
                    // El carro tiene cuatro puestos físicos: conductor y tres puestos comercializables.
                    'total_asientos' => $esAgencia ? 40 : 3,
                    'es_plantilla' => false,
                    'estatus' => 1,
                ],
            );

            $transporte->amenidades()->sync($amenidadesPermitidas->pluck('id')->all());
        }
    }

    private function crearRutas(Empresa $empresa, int $numero, $terminales): void
    {
        $secuenciasAgencia = [
            [0, 1, 2, 3, 4],
            [0, 1, 2],
            [1, 2, 3, 4],
            [4, 3, 2, 1, 0],
            [2, 3, 4],
        ];
        $secuencias = $empresa->tipo_entidad === Empresa::AGENCIA_AUTOBUS
            ? $secuenciasAgencia
            : [[$numero % $terminales->count(), ($numero + 2) % $terminales->count()]];

        foreach ($secuencias as $secuencia) {
            $viaje = Viaje::updateOrCreate(
                [
                    'empresa_id' => $empresa->id,
                    'origen_terminal_id' => $terminales[$secuencia[0]]->id,
                    'destino_terminal_id' => $terminales[$secuencia[array_key_last($secuencia)]]->id,
                ],
                [
                    'duracion_estimada' => sprintf('%02d:30:00', max(2, count($secuencia) * 2 - 1)),
                    'comentario' => collect($secuencia)
                        ->map(fn ($terminalIndice, $orden) => ($orden + 1).'. '.$terminales[$terminalIndice]->nombre)
                        ->implode(PHP_EOL),
                    'estatus' => 1,
                ],
            );

            foreach (array_slice($secuencia, 0, -1) as $orden => $origenIndice) {
                ViajeTramo::updateOrCreate(
                    ['viaje_id' => $viaje->id, 'orden' => $orden + 1],
                    [
                        'origen_terminal_id' => $terminales[$origenIndice]->id,
                        'destino_terminal_id' => $terminales[$secuencia[$orden + 1]]->id,
                        'duracion_estimada' => '01:30:00',
                    ],
                );
            }
        }
    }
}
