<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pasajes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('reserva_id');
            $table->unsignedBigInteger('viajero_id');
            $table->unsignedInteger('numero_asiento')->nullable();
            $table->decimal('precio_base', 12, 2);
            $table->decimal('descuento', 12, 2);
            $table->decimal('precio_final', 12, 2);
            $table->decimal('tasa_servicio', 12, 2)->default(0);
            $table->unsignedBigInteger('tasa_servicio_id')->nullable();
            $table->unsignedTinyInteger('tipo_servicio')->nullable();
            $table->decimal('valor_servicio', 12, 2)->nullable();
            $table->decimal('base_tasa_servicio', 12, 2)->nullable();
            $table->string('codigo_qr_token')->unique();
            $table->boolean('abordado')->default(false);
            $table->dateTime('fecha_abordaje')->nullable();
            $table->timestamps();
            $table->foreign('tasa_servicio_id')->references('id')->on('tasas_servicio')->onUpdate('cascade')->onDelete('restrict');
            $table->foreign('reserva_id')->references('id')->on('reservas')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('viajero_id')->references('id')->on('viajeros')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pasajes');
    }
};
