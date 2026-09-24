<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('pagos')) {
            DB::table('pagos')->orderBy('id')->each(function ($pago) {
                $reserva = DB::table('reservas')->where('id', $pago->reserva_id)->first();

                DB::table('pagos_reservas')->updateOrInsert(['id' => $pago->id], [
                    'reserva_id' => $pago->reserva_id,
                    'total' => $pago->monto,
                    'tasa_servicio' => $reserva?->tasa_servicio ?? 0,
                    'metodo_pago' => 1,
                    'referencia_pago' => $pago->referencia,
                    'fecha_pago' => $pago->fecha_pago,
                    'comprobante' => $pago->comprobante,
                    'created_at' => $pago->created_at,
                    'updated_at' => $pago->updated_at,
                ]);
            });
        }

        if (Schema::hasColumn('reembolsos', 'pago_id')) {
            Schema::table('reembolsos', function (Blueprint $table) {
                $table->dropForeign(['pago_id']);
                $table->renameColumn('pago_id', 'pago_reserva_id');
            });
            Schema::table('reembolsos', function (Blueprint $table) {
                $table->foreign('pago_reserva_id')->references('id')->on('pagos_reservas')->onUpdate('cascade')->onDelete('restrict');
            });
        }

        if (Schema::hasColumn('reservas', 'metodo_pago')) {
            Schema::table('reservas', function (Blueprint $table) {
                $table->dropColumn('metodo_pago');
            });
        }

        if (Schema::hasColumn('empresas', 'datos_bancarios')) {
            Schema::table('empresas', function (Blueprint $table) {
                $table->dropColumn('datos_bancarios');
            });
        }

        if (Schema::hasTable('sections_empresa')) {
            DB::table('sections_empresa')->where('url', 'retiros')->delete();
        }
        if (Schema::hasTable('groups_admin')) {
            DB::table('groups_admin')->where('url', 'configuracion')->delete();
        }

        Schema::dropIfExists('movimientos');
        Schema::dropIfExists('retiros');
        Schema::dropIfExists('pagos');
        Schema::dropIfExists('configuraciones');
    }

    public function down(): void
    {
        // La migración elimina módulos obsoletos y no recrea sus estructuras al revertir.
    }
};
