<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permisos_usuarios_empresa', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('usuario_empresa_id');
            $table->unsignedBigInteger('permiso_id');
            $table->timestamps();
            $table->foreign('usuario_empresa_id')->references('id')->on('usuarios_empresa')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('permiso_id')->references('id')->on('permissions_empresa')->onUpdate('cascade')->onDelete('cascade');
            $table->unique(['usuario_empresa_id', 'permiso_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permisos_usuarios_empresa');
    }
};
