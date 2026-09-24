<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reservas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('usuario_id');
            $table->unsignedBigInteger('programacion_id');
            $table->unsignedBigInteger('cupon_id')->nullable();
            $table->string('codigo_referencia')->unique();
            $table->decimal('monto_pasajes', 12, 2);
            $table->decimal('descuento_aplicado', 12, 2);
            $table->decimal('tasa_servicio', 12, 2);
            $table->decimal('monto_total', 12, 2);
            $table->enum('estado_pago', [1, 2, 3, 4, 5, 6])->default(1);
            $table->dateTime('fecha_compra');
            $table->dateTime('fecha_expiracion')->nullable();
            $table->unsignedTinyInteger('metodo_pago')->nullable();
            $table->json('comentarios_auditoria')->nullable();
            $table->index(['fecha_compra', 'estado_pago']);
            $table->timestamps();
            $table->foreign('usuario_id')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('programacion_id')->references('id')->on('programaciones')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('cupon_id')->references('id')->on('cupones')->onUpdate('cascade')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservas');
    }
};
