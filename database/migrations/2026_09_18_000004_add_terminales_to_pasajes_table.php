<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pasajes', function (Blueprint $table) {
            $table->unsignedBigInteger('origen_terminal_id')->nullable()->after('viajero_id');
            $table->unsignedBigInteger('destino_terminal_id')->nullable()->after('origen_terminal_id');

            $table->foreign('origen_terminal_id')->references('id')->on('terminales')->onUpdate('cascade')->onDelete('restrict');
            $table->foreign('destino_terminal_id')->references('id')->on('terminales')->onUpdate('cascade')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::table('pasajes', function (Blueprint $table) {
            $table->dropForeign(['origen_terminal_id']);
            $table->dropForeign(['destino_terminal_id']);
            $table->dropColumn(['origen_terminal_id', 'destino_terminal_id']);
        });
    }
};
