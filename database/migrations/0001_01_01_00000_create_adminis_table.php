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
        Schema::create('admins', function (Blueprint $table) {

            $table->id();

            $table->string('name');

            $table->string('username')->unique();

            $table->string('password');

            $table->string('remember_token', 100)->nullable();

            $table->string('email')->unique();

            $table->unsignedTinyInteger('level')->default(3); // 1: root, 2: superadmin, 3: permisos asignados

            $table->tinyInteger('status')->default(1); // 1: active, 2: inactive, 0: deleted

            $table->timestamps();

        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admins');
    }
};
