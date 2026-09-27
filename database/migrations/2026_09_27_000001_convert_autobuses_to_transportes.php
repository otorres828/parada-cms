<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('programaciones', fn (Blueprint $table) => $table->dropForeign(['autobus_id']));
        Schema::table('amenidad_autobus', function (Blueprint $table) {
            $table->dropForeign(['autobus_id']);
            $table->dropForeign(['amenidad_id']);
            $table->dropUnique(['autobus_id', 'amenidad_id']);
        });
        Schema::table('autobuses', fn (Blueprint $table) => $table->dropForeign(['empresa_id']));
        Schema::rename('autobuses', 'transportes');
        Schema::rename('amenidad_autobus', 'amenidad_transporte');
        Schema::table('programaciones', fn (Blueprint $table) => $table->renameColumn('autobus_id', 'transporte_id'));
        Schema::table('amenidad_transporte', fn (Blueprint $table) => $table->renameColumn('autobus_id', 'transporte_id'));
        Schema::table('transportes', function (Blueprint $table) {
            $table->string('tipo_transporte', 10)->default('autobus')->index();
            $table->foreign('empresa_id')->references('id')->on('empresas')->cascadeOnUpdate()->cascadeOnDelete();
        });
        Schema::table('programaciones', fn (Blueprint $table) => $table->foreign('transporte_id')->references('id')->on('transportes')->cascadeOnUpdate()->cascadeOnDelete());
        Schema::table('amenidad_transporte', function (Blueprint $table) {
            $table->foreign('transporte_id')->references('id')->on('transportes')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreign('amenidad_id')->references('id')->on('amenidades')->cascadeOnUpdate()->cascadeOnDelete();
            $table->unique(['transporte_id', 'amenidad_id']);
        });
        Schema::table('empresas', fn (Blueprint $table) => $table->string('tipo_entidad', 20)->default('agencia_autobus'));

        // Completa el tipo en las órdenes existentes sin alterar sus importes congelados.
        DB::table('ordenes_cobro')->orderBy('id')->chunkById(100, function ($ordenes) {
            foreach ($ordenes as $orden) {
                $reservas = json_decode($orden->reservas_incluidas ?? '[]', true) ?? [];
                $tipos = DB::table('reservas')->join('programaciones', 'programaciones.id', '=', 'reservas.programacion_id')
                    ->join('transportes', 'transportes.id', '=', 'programaciones.transporte_id')
                    ->whereIn('reservas.id', array_column($reservas, 'reserva_id'))
                    ->pluck('transportes.tipo_transporte', 'reservas.id');
                foreach ($reservas as &$reserva) {
                    $reserva['tipo_transporte'] ??= $tipos[$reserva['reserva_id']] ?? null;
                }
                unset($reserva);
                DB::table('ordenes_cobro')->where('id', $orden->id)->update(['reservas_incluidas' => json_encode($reservas, JSON_THROW_ON_ERROR)]);
            }
        });

        foreach (['sections_admin', 'sections_empresa'] as $tabla) {
            DB::table($tabla)->where('url', 'autobuses')->update(['url' => 'transportes', 'name' => 'Transportes']);
        }
        foreach (['permissions_admin', 'permissions_empresa'] as $tabla) {
            DB::table($tabla)->where('name', 'like', '%Autobus%')->orWhere('name', 'like', '%Autobús%')->get()->each(function ($permiso) use ($tabla) {
                DB::table($tabla)->where('id', $permiso->id)->update(['name' => str_replace(['Autobuses', 'Autobus', 'Autobús'], ['Transportes', 'Transporte', 'Transporte'], $permiso->name)]);
            });
        }
    }

    public function down(): void
    {
        foreach (['sections_admin', 'sections_empresa'] as $tabla) {
            DB::table($tabla)->where('url', 'transportes')->update(['url' => 'autobuses', 'name' => 'Autobuses']);
        }
        foreach (['permissions_admin', 'permissions_empresa'] as $tabla) {
            DB::table($tabla)->where('name', 'like', '%Transporte%')->get()->each(function ($permiso) use ($tabla) {
                DB::table($tabla)->where('id', $permiso->id)->update(['name' => str_replace(['Transportes', 'Transporte'], ['Autobuses', 'Autobus'], $permiso->name)]);
            });
        }
        Schema::table('empresas', fn (Blueprint $table) => $table->dropColumn('tipo_entidad'));
        Schema::table('programaciones', fn (Blueprint $table) => $table->dropForeign(['transporte_id']));
        Schema::table('amenidad_transporte', function (Blueprint $table) {
            $table->dropForeign(['transporte_id']);
            $table->dropForeign(['amenidad_id']);
            $table->dropUnique(['transporte_id', 'amenidad_id']);
        });
        Schema::table('transportes', function (Blueprint $table) {
            $table->dropForeign(['empresa_id']);
            $table->dropIndex(['tipo_transporte']);
            $table->dropColumn('tipo_transporte');
        });
        Schema::table('programaciones', fn (Blueprint $table) => $table->renameColumn('transporte_id', 'autobus_id'));
        Schema::table('amenidad_transporte', fn (Blueprint $table) => $table->renameColumn('transporte_id', 'autobus_id'));
        Schema::rename('amenidad_transporte', 'amenidad_autobus');
        Schema::rename('transportes', 'autobuses');
        Schema::table('autobuses', fn (Blueprint $table) => $table->foreign('empresa_id')->references('id')->on('empresas')->cascadeOnUpdate()->cascadeOnDelete());
        Schema::table('programaciones', fn (Blueprint $table) => $table->foreign('autobus_id')->references('id')->on('autobuses')->cascadeOnUpdate()->cascadeOnDelete());
        Schema::table('amenidad_autobus', function (Blueprint $table) {
            $table->foreign('autobus_id')->references('id')->on('autobuses')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreign('amenidad_id')->references('id')->on('amenidades')->cascadeOnUpdate()->cascadeOnDelete();
            $table->unique(['autobus_id', 'amenidad_id']);
        });
    }
};
