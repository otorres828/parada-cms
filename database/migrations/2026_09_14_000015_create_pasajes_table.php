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
            $table->unsignedBigInteger('viajero_id')->nullable();
            $table->unsignedInteger('numero_asiento')->nullable();
            $table->decimal('precio_base', 12, 2);
            $table->decimal('descuento', 12, 2);
            $table->decimal('subtotal', 12, 2);
            $table->decimal('tasa_servicio', 12, 2)->default(0);
            $table->decimal('total', 12, 2);
            $table->json('servicio_json')->nullable();
            $table->string('localizador')->unique();
            $table->boolean('abordado')->default(false);
            $table->time('hora_abordaje')->nullable();
            $table->timestamps();
            $table->unique(['reserva_id', 'numero_asiento']);
            $table->foreign('reserva_id')->references('id')->on('reservas')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('viajero_id')->references('id')->on('viajeros')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pasajes');
    }
};
