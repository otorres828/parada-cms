<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permissions_empresa', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('section_id');
            $table->string('name', 100);
            $table->string('url', 100);
            $table->boolean('status')->default(true);
            $table->unique(['section_id', 'url']);
            $table->foreign('section_id')->references('id')->on('sections_empresa')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permissions_empresa');
    }
};
