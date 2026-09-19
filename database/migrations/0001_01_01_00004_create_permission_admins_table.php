<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permission_admin_admin', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('permission_id');
            $table->unsignedBigInteger('admin_id');
            $table->boolean('status')->default(true);
            $table->unique(['permission_id', 'admin_id']);
            $table->foreign('permission_id')->references('id')->on('permissions_admin')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('admin_id')->references('id')->on('admins')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permission_admin_admin');
    }
};
