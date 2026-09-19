<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tasas_servicio', function (Blueprint $table) {
            $table->id();
            $table->decimal('monto_minimo', 12, 2);
            $table->decimal('monto_maximo', 12, 2)->nullable();
            $table->decimal('cantidad', 12, 2);
            $table->boolean('estatus')->default(true);
            $table->unsignedTinyInteger('tipo_servicio')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tasas_servicio');
    }
};
