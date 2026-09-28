<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('preguntas_frecuentes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('categoria_pregunta_frecuente_id');
            $table->string('pregunta');
            $table->string('slug')->unique();
            $table->text('resumen');
            $table->longText('respuesta');
            $table->text('palabras_clave')->nullable();
            $table->boolean('destacada')->default(false);
            $table->unsignedInteger('orden')->default(0);
            $table->unsignedTinyInteger('estatus')->default(1);
            $table->timestamps();

            $table->foreign('categoria_pregunta_frecuente_id')
                ->references('id')
                ->on('categorias_preguntas_frecuentes')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('preguntas_frecuentes');
    }
};
