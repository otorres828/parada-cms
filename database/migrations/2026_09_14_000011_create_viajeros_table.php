<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('viajeros', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('usuario_id');
            $table->string('nombre');
            $table->string('apellido');
            $table->string('documento_identidad')->nullable();
            $table->date('fecha_nacimiento');
            $table->enum('tipo_pasajero', ['adulto', 'nino', 'infante'])->default('adulto');
            $table->timestamps();
            $table->foreign('usuario_id')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('viajeros');
    }
};
