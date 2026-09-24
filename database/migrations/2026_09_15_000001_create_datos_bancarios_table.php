<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('datos_bancarios', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id');
            $table->unsignedTinyInteger('tipo');
            $table->string('banco');
            $table->string('nombre_titular');
            $table->enum('tipo_titular', ['juridico', 'extranjero', 'personal']);
            $table->string('numero_documento');
            $table->string('numero_cuenta_telefono');
            $table->enum('tipo_cuenta', ['corriente', 'ahorro'])->nullable();
            $table->unsignedTinyInteger('estatus')->default(1);
            $table->timestamps();

            $table->foreign('empresa_id')
                ->references('id')
                ->on('empresas')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('datos_bancarios');
    }
};
