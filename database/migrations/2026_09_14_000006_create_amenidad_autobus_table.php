<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('amenidad_autobus', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('autobus_id');
            $table->unsignedBigInteger('amenidad_id');
            $table->timestamps();
            $table->foreign('autobus_id')->references('id')->on('autobuses')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('amenidad_id')->references('id')->on('amenidades')->onUpdate('cascade')->onDelete('cascade');
            $table->unique(['autobus_id', 'amenidad_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('amenidad_autobus');
    }
};
