<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {

            $table->id();

            $table->string('name');

            $table->string('lastname');

            $table->string('email')->unique();
            $table->string('telefono')->nullable();

            $table->date('date_birth')->nullable();

            $table->enum('sex', ['1', '2'])->default(1); // 1: Masculino, 2: Femenino

            $table->string('password')->nullable();

            $table->string('remember_token', 100)->nullable();

            $table->tinyInteger('status')->default(1); // 1: activo, 2: inactivo, 0: eliminado

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
