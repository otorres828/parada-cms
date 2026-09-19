<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Pelea;
use App\Models\PeleaUsuario;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class InyectarApuestasCommand extends Command
{
    // Firma nativa con los parámetros posicionales requeridos
    protected $signature = 'sorteo:inject-bets {evento_id} {pelea_id}';

    protected $description = 'Inyecta apuestas simuladas verificando y debitando el saldo real disponible de los usuarios.';

    public function handle()
    {
        // El tipado se recupera directamente de forma segura
        $eventoId = (int) $this->argument('evento_id');
        $peleaId  = (int) $this->argument('pelea_id');

        // --- VALIDACIÓN DE COHERENCIA EN BASE DE DATOS ---
        $pelea = Pelea::with('evento')->where('id', $peleaId)->where('evento_id', $eventoId)->first();

        if (!$pelea) {
            $this->error("❌ No se encontró la Pelea #{$peleaId} asociada al Evento #{$eventoId}.");
            return Command::FAILURE;
        }

        if ($pelea->estatus_pelea == Pelea::ESTATUS_PELEA_FINALIZADA) {
            $this->error("⚠️ Operación abortada: La Pelea #{$peleaId} ya está FINALIZADA y consolidada.");
            return Command::FAILURE;
        }

        $this->info("🎯 Iniciando inyección de apuestas con validación de saldo para la Pelea #{$peleaId}...");

        $modalidades = DB::table('modalidades_apuestas')->get()->keyBy('id');
        $usuarios = User::where('status', 1)->get();

        if ($usuarios->isEmpty()) {
            $this->error("❌ No existen usuarios activos registrados en el sistema.");
            return Command::FAILURE;
        }

        $contadorInyecciones = 0;
        $porcentajeCasa = $pelea->evento->porcentaje_casa ?? 10;

        foreach ($usuarios as $user) {
            // 1. Regla de unicidad: Verificar si el usuario ya apostó en este combate
            $existeApuesta = DB::table('pelea_usuario')
                ->where('pelea_id', $pelea->id)
                ->where('user_id', $user->id)
                ->exists();

            if ($existeApuesta) {
                continue;
            }

            // 2. Control de Fondos Disponibles
            if ($user->balance <= 200) {
                continue;
            }

            // Forzamos que el monto base no supere el balance real
            $montoMaximoApostable = min(2500, (float)$user->balance);
            $montoApostado = rand(200, $montoMaximoApostable);

            $modalidadApuesta = rand(1,2); // 1: Doy, 2: Agarro
            $modId = ($modalidadApuesta === 1) ? 1 : rand(2, 5);
            $porcentajeMod = $modalidades[$modId]->porcentaje ?? 100;
            $galloElegido = rand(1, 2);

            // Cálculos financieros base
            $gananciaPotencial = match ($modalidadApuesta) {
                1 => $montoApostado,
                2 => $montoApostado * ($porcentajeMod / 100),
                3 => $montoApostado,
            };

            $perdidaPotencial = match ($modalidadApuesta) {
                1 => $montoApostado,
                2 => $montoApostado,
                3 => $montoApostado * ($porcentajeMod / 100),
            };

            // 3. Validación de Riesgo Máximo en Billetera
            if ($user->balance < $perdidaPotencial) {
                if ($modalidadApuesta === 3) {
                    $montoApostado = (int)($user->balance / ($porcentajeMod / 100));
                    $perdidaPotencial = $user->balance;
                    $gananciaPotencial = $montoApostado;
                } else {
                    $montoApostado = (int)$user->balance;
                    $perdidaPotencial = $user->balance;
                    $gananciaPotencial = ($modalidadApuesta === 2) ? $montoApostado * ($porcentajeMod / 100) : $montoApostado;
                }

                if ($montoApostado < 200) {
                    continue;
                }
            }

            $gananciaReal = $gananciaPotencial * (1 - ($porcentajeCasa / 100));

            // Transacción por lote atómico individual
            DB::transaction(function () use ($pelea, $user, $galloElegido, $modId, $modalidadApuesta, $porcentajeCasa, $montoApostado, $gananciaPotencial, $perdidaPotencial, $gananciaReal) {

                $referencia = "EV{$pelea->evento_id}P{$pelea->id}U{$user->id}";

                DB::table('pelea_usuario')->insert([
                    'evento_id' => $pelea->evento_id,
                    'pelea_id' => $pelea->id,
                    'user_id' => $user->id,
                    'tipo_gallo' => $galloElegido,
                    'modalidad_id' => $modId,
                    'modalidad_apuesta' => $modalidadApuesta,
                    'porcentaje_casa' => $porcentajeCasa,
                    'cantidad_apostada' => $montoApostado,
                    'ganancia_potencial' => $gananciaPotencial,
                    'perdida_potencial' => $perdidaPotencial,
                    'ganancia_real' => $gananciaReal,
                    'referencia' => $referencia,
                    'estatus' => PeleaUsuario::ESTATUS_ACTIVA,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

            });

            $contadorInyecciones++;
        }

        $this->info("✅ Inyección finalizada. Se procesaron {$contadorInyecciones} apuestas reales debitadas del saldo disponible.");

        return Command::SUCCESS;
    }
}
