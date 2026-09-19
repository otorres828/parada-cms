<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('terminales', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('estado_id');
            $table->string('nombre');
            $table->text('direccion');
            $table->decimal('latitud', 10, 7);
            $table->decimal('longitud', 11, 7);
            $table->boolean('estatus')->default(true);
            $table->timestamps();
            $table->foreign('estado_id')->references('id')->on('estados')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('terminales');
    }
};
