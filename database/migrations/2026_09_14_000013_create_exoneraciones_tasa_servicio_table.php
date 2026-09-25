<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exoneraciones_tasa_servicio', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id');
            $table->dateTime('fecha_desde');
            $table->dateTime('fecha_hasta')->nullable();
            $table->string('motivo');
            $table->unsignedTinyInteger('estatus')->default(1);
            $table->timestamps();
            $table->index(['empresa_id', 'estatus', 'fecha_desde', 'fecha_hasta'], 'exoneraciones_empresa_vigencia_index');
            $table->foreign('empresa_id')->references('id')->on('empresas')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exoneraciones_tasa_servicio');
    }
};
