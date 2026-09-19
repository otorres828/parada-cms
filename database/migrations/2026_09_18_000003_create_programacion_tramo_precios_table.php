<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('programacion_tramo_precios', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('programacion_id');
            $table->unsignedBigInteger('origen_terminal_id');
            $table->unsignedBigInteger('destino_terminal_id');
            $table->decimal('precio', 12, 2);
            $table->unsignedInteger('asientos_maximos_permitidos')->nullable();
            $table->timestamps();

            $table->foreign('programacion_id')->references('id')->on('programaciones')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('origen_terminal_id')->references('id')->on('terminales')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('destino_terminal_id')->references('id')->on('terminales')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('programacion_tramo_precios');
    }
};
