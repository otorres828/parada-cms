<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('auditorias', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('admin_id')->nullable();
            $table->string('accion');
            $table->string('entidad');
            $table->unsignedBigInteger('entidad_id')->nullable();
            $table->json('datos')->nullable();
            $table->string('ip', 45)->nullable();
            $table->timestamps();
            $table->foreign('admin_id')->references('id')->on('admins')->onUpdate('cascade')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auditorias');
    }
};
