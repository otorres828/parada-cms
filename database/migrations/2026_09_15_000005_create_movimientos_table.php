<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('movimientos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id');
            $table->unsignedBigInteger('admin_id');
            $table->unsignedBigInteger('pago_id')->nullable();
            $table->unsignedBigInteger('retiro_id')->nullable()->unique();
            $table->unsignedBigInteger('reembolso_id')->nullable()->unique();
            $table->string('clave')->unique();
            $table->enum('tipo', ['venta', 'retiro', 'reembolso']);
            $table->decimal('monto', 12, 2);
            $table->char('moneda', 3);
            $table->text('descripcion');
            $table->timestamps();
            foreach (['empresa_id' => 'empresas', 'admin_id' => 'admins', 'pago_id' => 'pagos', 'retiro_id' => 'retiros', 'reembolso_id' => 'reembolsos'] as $key => $parent) {
                $table->foreign($key)->references('id')->on($parent)->onUpdate('cascade')->onDelete('restrict');
            }
            $table->index(['empresa_id', 'moneda']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movimientos');
    }
};
