<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('programaciones', function (Blueprint $table) {
            if (Schema::hasColumn('programaciones', 'precio_pasaje')) {
                $table->dropColumn('precio_pasaje');
            }
        });
    }

    public function down(): void
    {
        Schema::table('programaciones', function (Blueprint $table) {
            $table->decimal('precio_pasaje', 12, 2)->nullable()->after('asientos_disponibles');
        });
    }
};
