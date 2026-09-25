<?php

namespace App\Console\Commands;

use App\Models\Empresa;
use App\Services\OrdenCobroService;
use Illuminate\Console\Command;

class GenerarOrdenesCobro extends Command
{
    protected $signature = 'ordenes-cobro:generar
        {--empresa= : ID de una empresa específica}
        {--sin-notificaciones : Genera las órdenes sin enviar correos}';

    protected $description = 'Genera las órdenes de cobro correspondientes al día de corte';

    public function handle(OrdenCobroService $service): int
    {
        $query = Empresa::query()
            ->where('tipo_contrato', Empresa::CONTRATO_ELLOS_RECIBEN)
            ->where('dia_corte', now()->dayOfWeekIso)
            ->where('hora_corte', '<=', now()->format('H:i:s'))
            ->where('estatus', Empresa::ESTADO_ACTIVE);

        if ($this->option('empresa')) {
            $query->whereKey((int) $this->option('empresa'));
        }

        $creadas = 0;

        $query->orderBy('id')->each(function (Empresa $empresa) use ($service, &$creadas) {
            $orden = $service->generar(
                $empresa,
                now(),
                ! $this->option('sin-notificaciones'),
            );

            if (! $orden) {
                $this->line($empresa->nombre.': sin orden nueva.');

                return;
            }

            $creadas++;
            $this->info($empresa->nombre.': '.$orden->codigo.' por '.$orden->total.'.');
        });

        $this->newLine();
        $this->info('Órdenes creadas: '.$creadas.'.');

        return self::SUCCESS;
    }
}
