<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('programaciones', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('viaje_id');
            $table->unsignedBigInteger('autobus_id');
            $table->date('fecha_salida');
            $table->time('hora_salida');
            $table->unsignedInteger('asientos_totales');
            $table->decimal('precio_pasaje', 12, 2);
            $table->unsignedTinyInteger('estatus')->default(1);
            $table->timestamps();
            $table->foreign('viaje_id')->references('id')->on('viajes')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('autobus_id')->references('id')->on('autobuses')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('programaciones');
    }
};
