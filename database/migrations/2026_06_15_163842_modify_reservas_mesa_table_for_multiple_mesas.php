<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Crear tabla pivot
        Schema::create('reserva_mesas', function (Blueprint $table) {
            $table->uuid('reserva_id');
            $table->uuid('mesa_id');

            $table->foreign('reserva_id')->references('id')->on('reservas_mesa')->onDelete('cascade');
            $table->foreign('mesa_id')->references('id')->on('mesas')->onDelete('cascade');

            $table->primary(['reserva_id', 'mesa_id']);
        });

        // 2. Migrar datos existentes (si los hay)
        DB::table('reservas_mesa')->whereNotNull('mesa_id')->orderBy('creado_en')->chunk(100, function ($reservas) {
            $inserts = [];
            foreach ($reservas as $reserva) {
                $inserts[] = [
                    'reserva_id' => $reserva->id,
                    'mesa_id' => $reserva->mesa_id,
                ];
            }
            if (count($inserts) > 0) {
                DB::table('reserva_mesas')->insert($inserts);
            }
        });

        // 3. Modificar reservas_mesa
        Schema::table('reservas_mesa', function (Blueprint $table) {
            $table->dropIndex('idx_disponibilidad');
            
            if (DB::getDriverName() !== 'sqlite') {
                $table->dropForeign(['mesa_id']);
                $table->dropColumn('mesa_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('reservas_mesa', function (Blueprint $table) {
            $table->uuid('mesa_id')->nullable()->index();
            $table->foreign('mesa_id')->references('id')->on('mesas')->onDelete('set null');
            $table->index(['mesa_id', 'fecha_reserva', 'estado'], 'idx_disponibilidad');
        });

        // Restaurar datos (solo 1 mesa por reserva en el down)
        DB::table('reserva_mesas')->orderBy('reserva_id')->chunk(100, function ($asignaciones) {
            foreach ($asignaciones as $asignacion) {
                // Solo asignará la última mesa si hay múltiples, al hacer rollback
                DB::table('reservas_mesa')
                    ->where('id', $asignacion->reserva_id)
                    ->update(['mesa_id' => $asignacion->mesa_id]);
            }
        });

        Schema::dropIfExists('reserva_mesas');
    }
};
