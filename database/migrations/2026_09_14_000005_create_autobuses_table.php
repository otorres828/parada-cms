<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('autobuses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id');
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
        Schema::dropIfExists('autobuses');
    }
};
