<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ordenes_cobro', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id');
            $table->unsignedBigInteger('admin_id')->nullable();
            $table->string('codigo', 30)->unique();
            $table->dateTime('periodo_desde');
            $table->dateTime('periodo_hasta');
            $table->dateTime('fecha_emision');
            $table->dateTime('fecha_vencimiento');
            $table->unsignedInteger('cantidad_reservas')->default(0);
            $table->decimal('total', 12, 2);
            $table->unsignedTinyInteger('estatus')->default(1);
            $table->string('referencia_pago')->nullable();
            $table->string('comprobante')->nullable();
            $table->dateTime('fecha_pago_reportado')->nullable();
            $table->dateTime('fecha_aprobacion')->nullable();
            $table->dateTime('notificacion_emitida_at')->nullable();
            $table->dateTime('recordatorio_48_at')->nullable();
            $table->dateTime('recordatorio_24_at')->nullable();
            $table->longText('comentarios')->nullable();
            $table->json('reservas_incluidas')->nullable();
            $table->timestamps();

            $table->unique(['empresa_id', 'periodo_desde', 'periodo_hasta'], 'orden_cobro_periodo_unique');
            $table->index(['empresa_id', 'estatus']);
            $table->index(['fecha_vencimiento', 'estatus']);

            $table->foreign('empresa_id')
                ->references('id')
                ->on('empresas')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->foreign('admin_id')
                ->references('id')
                ->on('admins')
                ->onUpdate('cascade')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ordenes_cobro');
    }
};
