<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('configuracion_cupones', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id')->nullable();
            $table->string('nombre_campana');
            $table->string('tipo_cupon');
            $table->string('modalidad');
            $table->string('codigo_base');
            $table->unsignedInteger('cantidad_generar');
            $table->string('tipo_descuento');
            $table->decimal('monto_descuento', 12, 2);
            $table->string('aplica_a');
            $table->dateTime('fecha_inicio');
            $table->dateTime('fecha_fin');
            $table->boolean('estatus')->default(true);
            $table->timestamps();
            $table->foreign('empresa_id')->references('id')->on('empresas')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('configuracion_cupones');
    }
};
