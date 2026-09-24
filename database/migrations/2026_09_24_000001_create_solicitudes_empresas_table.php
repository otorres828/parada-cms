<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('solicitudes_empresas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('empresa');
            $table->string('cargo');
            $table->string('telefono', 50);
            $table->string('email');
            $table->string('ciudad')->nullable();
            $table->text('mensaje')->nullable();
            $table->enum('estatus', [1, 2, 3])->default(1);
            $table->timestamps();
            $table->index(['estatus', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('solicitudes_empresas');
    }
};
