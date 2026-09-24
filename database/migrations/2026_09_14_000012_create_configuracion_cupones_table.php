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
            $table->unsignedBigInteger('empresa_id')->nullable(); // Si empresa_id es nulo, significa que es un cupón global para todas las empresas
            $table->string('nombre_campana');
            $table->integer('tipo_cupon')->default(1); // 1: random 2: custom (personalizado)
            $table->enum('tipo_descuento', ['monto_fijo', 'porcentaje'])->default('monto_fijo');
            $table->enum('modalidad', ['GENERAL', 'PRIMERA_COMPRA', 'USUARIO_NUEVO']);
            $table->enum('aplica_en', ['reserva', 'pasajes'])->default('reserva');
            $table->string('codigo_personalizado')->nullable()->unique();
            $table->unsignedInteger('cantidad_generar');
            $table->decimal('monto_descuento', 12, 2);
            $table->dateTime('fecha_inicio');
            $table->dateTime('fecha_fin');
            $table->integer('estatus')->default(1); // 1: activo, 2: inactivo, 0: eliminado
            $table->timestamps();
            $table->foreign('empresa_id')->references('id')->on('empresas')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('configuracion_cupones');
    }
};
