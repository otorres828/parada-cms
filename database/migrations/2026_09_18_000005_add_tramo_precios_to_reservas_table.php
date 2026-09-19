<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservas', function (Blueprint $table) {
            $table->unsignedBigInteger('origen_terminal_id')->nullable()->after('programacion_id');
            $table->unsignedBigInteger('destino_terminal_id')->nullable()->after('origen_terminal_id');
            $table->unsignedBigInteger('programacion_tramo_precio_id')->nullable()->after('destino_terminal_id');

            $table->foreign('origen_terminal_id')->references('id')->on('terminales')->onUpdate('cascade')->onDelete('restrict');
            $table->foreign('destino_terminal_id')->references('id')->on('terminales')->onUpdate('cascade')->onDelete('restrict');
            $table->foreign('programacion_tramo_precio_id')->references('id')->on('programacion_tramo_precios')->onUpdate('cascade')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('reservas', function (Blueprint $table) {
            $table->dropForeign(['origen_terminal_id']);
            $table->dropForeign(['destino_terminal_id']);
            $table->dropForeign(['programacion_tramo_precio_id']);
            $table->dropColumn(['origen_terminal_id', 'destino_terminal_id', 'programacion_tramo_precio_id']);
        });
    }
};
