<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('amenidad_transporte', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('transporte_id');
            $table->unsignedBigInteger('amenidad_id');
            $table->timestamps();
            $table->foreign('transporte_id')->references('id')->on('transportes')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('amenidad_id')->references('id')->on('amenidades')->onUpdate('cascade')->onDelete('cascade');
            $table->unique(['transporte_id', 'amenidad_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('amenidad_transporte');
    }
};
