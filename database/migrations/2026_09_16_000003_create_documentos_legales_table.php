<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documentos_legales', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id');
            $table->unsignedBigInteger('admin_id')->nullable();
            $table->string('titulo');
            $table->string('tipo', 30);
            $table->text('observaciones')->nullable();
            $table->string('archivo');
            $table->string('nombre_original');
            $table->string('mime', 100);
            $table->unsignedBigInteger('tamano');
            $table->timestamps();
            $table->index(['empresa_id', 'tipo']);
            $table->foreign('empresa_id')->references('id')->on('empresas')->onUpdate('cascade')->onDelete('restrict');
            $table->foreign('admin_id')->references('id')->on('admins')->onUpdate('cascade')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documentos_legales');
    }
};
