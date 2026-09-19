<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('usuarios_empresa', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id');
            $table->string('nombre');
            $table->string('email')->unique();
            $table->string('password');
            $table->boolean('es_admin')->default(false);
            $table->boolean('estatus')->default(true);
            $table->rememberToken();
            $table->timestamps();
            $table->foreign('empresa_id')->references('id')->on('empresas')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usuarios_empresa');
    }
};
