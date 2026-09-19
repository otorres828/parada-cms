<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('viaje_tramos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('viaje_id');
            $table->unsignedBigInteger('origen_terminal_id');
            $table->unsignedBigInteger('destino_terminal_id');
            $table->unsignedInteger('orden')->default(1);
            $table->time('duracion_estimada')->nullable();
            $table->timestamps();

            $table->foreign('viaje_id')->references('id')->on('viajes')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('origen_terminal_id')->references('id')->on('terminales')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('destino_terminal_id')->references('id')->on('terminales')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('viaje_tramos');
    }
};
