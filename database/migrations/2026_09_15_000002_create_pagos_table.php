<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pagos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('reserva_id')->unique();
            $table->unsignedBigInteger('empresa_id');
            $table->unsignedBigInteger('admin_id');
            $table->decimal('monto', 12, 2);
            $table->decimal('neto_empresa', 12, 2);
            $table->decimal('comision', 12, 2);
            $table->char('moneda', 3);
            $table->string('referencia')->unique();
            $table->string('metodo');
            $table->string('comprobante')->nullable();
            $table->text('comentario')->nullable();
            $table->dateTime('fecha_pago');
            $table->timestamps();
            $table->foreign('reserva_id')->references('id')->on('reservas')->onUpdate('cascade')->onDelete('restrict');
            $table->foreign('empresa_id')->references('id')->on('empresas')->onUpdate('cascade')->onDelete('restrict');
            $table->foreign('admin_id')->references('id')->on('admins')->onUpdate('cascade')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pagos');
    }
};
