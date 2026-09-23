<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reembolsos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pago_reserva_id')->unique();
            $table->unsignedBigInteger('empresa_id');
            $table->unsignedBigInteger('admin_id');
            $table->unsignedBigInteger('revisado_por')->nullable();
            $table->decimal('monto', 12, 2);
            $table->char('moneda', 3);
            $table->enum('estatus', ['pendiente', 'aprobado', 'pagado', 'rechazado'])->default('pendiente');
            $table->text('motivo');
            $table->text('comentario')->nullable();
            $table->string('referencia')->nullable()->unique();
            $table->string('comprobante')->nullable();
            $table->dateTime('fecha_resolucion')->nullable();
            $table->timestamps();
            foreach (['pago_reserva_id' => 'pagos_reservas', 'empresa_id' => 'empresas', 'admin_id' => 'admins', 'revisado_por' => 'admins'] as $key => $parent) {
                $table->foreign($key)->references('id')->on($parent)->onUpdate('cascade')->onDelete('restrict');
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reembolsos');
    }
};
