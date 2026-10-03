<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pagos_reservas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('reserva_id')->index();
            $table->decimal('monto_recibido', 12, 2)->nullable();
            $table->decimal('total', 12, 2);
            $table->decimal('tasa_servicio', 12, 2)->default(0.00);
            $table->unsignedBigInteger('metodo_pago')->nullable();
            $table->enum('tipo_pago', [1, 2, 3, 4])->default(1);
            $table->string('referencia_pago')->nullable();
            $table->dateTime('fecha_pago');
            $table->string('comprobante')->nullable();
            $table->timestamps();

            $table->foreign('reserva_id')
                ->references('id')
                ->on('reservas')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->foreign('metodo_pago')
                ->references('id')
                ->on('datos_bancarios')
                ->onUpdate('cascade')
                ->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pagos_reservas');
    }
};
