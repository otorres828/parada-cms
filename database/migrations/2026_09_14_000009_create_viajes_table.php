<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('viajes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id');
            $table->unsignedBigInteger('origen_terminal_id');
            $table->unsignedBigInteger('destino_terminal_id');
            $table->time('duracion_estimada');
            $table->text('comentario')->nullable();
            $table->boolean('estatus')->default(true);
            $table->timestamps();
            $table->foreign('empresa_id')->references('id')->on('empresas')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('origen_terminal_id')->references('id')->on('terminales')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('destino_terminal_id')->references('id')->on('terminales')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('viajes');
    }
};
