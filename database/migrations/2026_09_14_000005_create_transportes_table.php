<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transportes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id');
            $table->string('tipo_transporte', 10)->default('autobus')->index();
            $table->string('placa')->nullable();
            $table->string('modelo');
            $table->string('tipo_asiento');
            $table->unsignedInteger('total_asientos');
            $table->boolean('es_plantilla')->default(false);
            $table->boolean('estatus')->default(true);
            $table->timestamps();
            $table->foreign('empresa_id')->references('id')->on('empresas')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transportes');
    }
};
