<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tipos_cambios', function (Blueprint $table) {
            $table->id();
            $table->decimal('valor_usd', 18, 2);
            $table->decimal('valor_eur', 18, 2);
            $table->decimal('valor', 18, 2)->default(1);
            $table->timestamp('timestamp')->useCurrent();
            $table->index('timestamp');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tipos_cambios');
    }
};
