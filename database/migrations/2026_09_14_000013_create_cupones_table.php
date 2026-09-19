<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cupones', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('configuracion_cupon_id');
            $table->unsignedBigInteger('usuario_id')->nullable();
            $table->string('codigo')->unique();
            $table->boolean('redimido')->default(false);
            $table->dateTime('fecha_redencion')->nullable();
            $table->timestamps();
            $table->foreign('configuracion_cupon_id')->references('id')->on('configuracion_cupones')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('usuario_id')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cupones');
    }
};
