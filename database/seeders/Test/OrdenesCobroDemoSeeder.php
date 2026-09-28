<?php

namespace Database\Seeders\Test;

use App\Models\Empresa;
use App\Models\OrdenCobro;
use App\Models\Reserva;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OrdenesCobroDemoSeeder extends Seeder
{
    private const TAMANO_LOTE = 100;

    public function run(): void
    {
        if (OrdenCobro::query()->exists()) {
            $this->command?->warn('Las órdenes de cobro históricas ya fueron generadas. Se omite para evitar duplicados.');

            return;
        }

        $desde = CarbonImmutable::parse((string) env('DEMO_FECHA_DESDE', '2026-01-01'))->startOfDay();
        $hasta = CarbonImmutable::parse(
            (string) env('DEMO_FECHA_HASTA', CarbonImmutable::today()->toDateString()),
        )->endOfDay();

        $empresas = Empresa::query()
            ->where('tipo_contrato', Empresa::CONTRATO_ELLOS_RECIBEN)
            ->orderBy('id')
            ->get();

        $reservas = Reserva::query()
            ->select([
                'reservas.id',
                'reservas.codigo_referencia',
                'reservas.tasa_servicio',
                'reservas.fecha_pago',
                'transportes.tipo_transporte',
                'viajes.empresa_id',
            ])
            ->join('programaciones', 'programaciones.id', '=', 'reservas.programacion_id')
            ->join('viajes', 'viajes.id', '=', 'programaciones.viaje_id')
            ->join('transportes', 'transportes.id', '=', 'programaciones.transporte_id')
            ->whereIn('viajes.empresa_id', $empresas->pluck('id'))
            ->whereBetween('reservas.fecha_pago', [$desde, $hasta])
            ->whereIn('reservas.estado_pago', [
                Reserva::ESTADO_PAGO_PAGADO,
                Reserva::ESTADO_PAGO_REPROGRAMADO,
                Reserva::ESTADO_PAGO_REEMBOLSADO,
            ])
            ->where('reservas.tasa_servicio', '>', 0)
            ->orderBy('reservas.fecha_pago')
            ->orderBy('reservas.id')
            ->get()
            ->groupBy('empresa_id');

        $siguienteId = ((int) DB::table('ordenes_cobro')->max('id')) + 1;
        $ordenes = [];

        foreach ($empresas as $empresa) {
            $reservasEmpresa = $reservas->get($empresa->id, collect());

            if ($reservasEmpresa->isEmpty()) {
                continue;
            }

            $primeraFecha = CarbonImmutable::parse($reservasEmpresa->first()->fecha_pago);
            $ultimoCorte = CarbonImmutable::parse($reservasEmpresa->last()->fecha_pago)
                ->nextOrSame(CarbonImmutable::SUNDAY)
                ->startOfDay();

            $periodoDesde = $primeraFecha->previousOrSame(CarbonImmutable::SUNDAY)->startOfDay();

            for ($fechaCorte = $periodoDesde->addWeek(); $fechaCorte->lessThanOrEqualTo($ultimoCorte); $fechaCorte = $fechaCorte->addWeek()) {
                $periodoHasta = $fechaCorte->subSecond();
                $reservasPeriodo = $reservasEmpresa->filter(function ($reserva) use ($periodoDesde, $periodoHasta) {
                    $fechaPago = CarbonImmutable::parse($reserva->fecha_pago);

                    return $fechaPago->betweenIncluded($periodoDesde, $periodoHasta);
                });

                if ($reservasPeriodo->isNotEmpty()) {
                    $fechaEmision = $fechaCorte;
                    $fechaVencimiento = $fechaEmision->next(CarbonImmutable::FRIDAY)->endOfDay();
                    $aprobada = $fechaVencimiento->isBefore($hasta);
                    $codigo = 'OC-'.$fechaEmision->format('Y').'-'.str_pad((string) $siguienteId, 6, '0', STR_PAD_LEFT);

                    $ordenes[] = [
                        'id' => $siguienteId,
                        'empresa_id' => $empresa->id,
                        'admin_id' => null,
                        'codigo' => $codigo,
                        'periodo_desde' => $periodoDesde->toDateTimeString(),
                        'periodo_hasta' => $periodoHasta->toDateTimeString(),
                        'fecha_emision' => $fechaEmision->toDateTimeString(),
                        'fecha_vencimiento' => $fechaVencimiento->toDateTimeString(),
                        'cantidad_reservas' => $reservasPeriodo->count(),
                        'total' => number_format((float) $reservasPeriodo->sum('tasa_servicio'), 2, '.', ''),
                        'estatus' => $aprobada ? OrdenCobro::ESTATUS_APROBADO : OrdenCobro::ESTATUS_EMITIDO,
                        'referencia_pago' => $aprobada ? 'COBRO-'.str_pad((string) $siguienteId, 12, '0', STR_PAD_LEFT) : null,
                        'comprobante' => null,
                        'fecha_pago_reportado' => $aprobada ? $fechaVencimiento->subDay()->setTime(10, 0)->toDateTimeString() : null,
                        'fecha_aprobacion' => $aprobada ? $fechaVencimiento->subDay()->setTime(12, 0)->toDateTimeString() : null,
                        'notificacion_emitida_at' => $fechaEmision->toDateTimeString(),
                        'recordatorio_48_at' => null,
                        'recordatorio_24_at' => null,
                        'comentarios' => null,
                        'reservas_incluidas' => json_encode(
                            $reservasPeriodo->map(function ($reserva) {
                                return [
                                    'reserva_id' => $reserva->id,
                                    'codigo_referencia' => $reserva->codigo_referencia,
                                    'tipo_transporte' => $reserva->tipo_transporte,
                                    'tasa_servicio' => (float) $reserva->tasa_servicio,
                                    'fecha_pago' => CarbonImmutable::parse($reserva->fecha_pago)->toDateTimeString(),
                                ];
                            })->values()->all(),
                            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES,
                        ),
                        'created_at' => $fechaEmision->toDateTimeString(),
                        'updated_at' => $aprobada
                            ? $fechaVencimiento->subDay()->setTime(12, 0)->toDateTimeString()
                            : $fechaEmision->toDateTimeString(),
                    ];

                    $siguienteId++;
                }

                $periodoDesde = $fechaCorte;
            }
        }

        DB::transaction(function () use ($ordenes) {
            foreach (array_chunk($ordenes, self::TAMANO_LOTE) as $lote) {
                DB::table('ordenes_cobro')->insert($lote);
            }
        });

        $this->command?->info(count($ordenes).' órdenes de cobro históricas generadas hasta la fecha indicada.');
    }
}
