<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('groups_admin', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('url', 100)->unique();
            $table->string('icon', 64)->nullable();
            $table->boolean('status')->default(true);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('groups_admin');
    }
};
