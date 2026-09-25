<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('empresas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('rif');
            $table->string('telefono');
            $table->string('email');
            $table->unsignedTinyInteger('tipo_contrato')->default(1);
            $table->unsignedTinyInteger('dia_corte')->nullable();
            $table->unsignedTinyInteger('dia_vencimiento')->nullable();
            $table->time('hora_corte')->default('00:00:00');
            $table->time('hora_vencimiento')->default('23:59:59');
            $table->dateTime('bloqueada_por_cobranza_at')->nullable();
            $table->boolean('estatus')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('empresas');
    }
};
