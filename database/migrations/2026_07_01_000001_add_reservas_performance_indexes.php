<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Migracion de rendimiento: indices compuestos para el modulo de reservas.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!$this->indexExists('reservas_mesa', 'idx_reservas_sucursal_fecha_estado')) {
            Schema::table('reservas_mesa', function (Blueprint $table) {
                $table->index(['sucursal_id', 'fecha_reserva', 'estado'], 'idx_reservas_sucursal_fecha_estado');
            });
        }
        if (!$this->indexExists('reservas_mesa', 'idx_reservas_fecha_horario')) {
            Schema::table('reservas_mesa', function (Blueprint $table) {
                $table->index(['fecha_reserva', 'hora_inicio', 'hora_fin'], 'idx_reservas_fecha_horario');
            });
        }
        if (!$this->indexExists('reservas_mesa', 'idx_reservas_estado_fecha')) {
            Schema::table('reservas_mesa', function (Blueprint $table) {
                $table->index(['estado', 'fecha_reserva'], 'idx_reservas_estado_fecha');
            });
        }
        if (!$this->indexExists('reservas_mesa', 'idx_reservas_estado_creacion')) {
            Schema::table('reservas_mesa', function (Blueprint $table) {
                $table->index(['estado', 'creado_en'], 'idx_reservas_estado_creacion');
            });
        }
        if (!$this->indexExists('reservas_mesa', 'idx_reservas_correo_cliente')) {
            Schema::table('reservas_mesa', function (Blueprint $table) {
                $table->index('correo_cliente', 'idx_reservas_correo_cliente');
            });
        }
        if (!$this->indexExists('pagos_reserva', 'idx_pagos_reserva_estado')) {
            Schema::table('pagos_reserva', function (Blueprint $table) {
                $table->index(['reserva_id', 'estado'], 'idx_pagos_reserva_estado');
            });
        }
    }

    public function down(): void
    {
        Schema::table('reservas_mesa', function (Blueprint $table) {
            $table->dropIndexIfExists('idx_reservas_sucursal_fecha_estado');
            $table->dropIndexIfExists('idx_reservas_fecha_horario');
            $table->dropIndexIfExists('idx_reservas_estado_fecha');
            $table->dropIndexIfExists('idx_reservas_estado_creacion');
            $table->dropIndexIfExists('idx_reservas_correo_cliente');
        });
        Schema::table('pagos_reserva', function (Blueprint $table) {
            $table->dropIndexIfExists('idx_pagos_reserva_estado');
        });
    }

    private function indexExists(string $table, string $indexName): bool
    {
        if (DB::getDriverName() === 'sqlite') {
            return !empty(DB::select("SELECT name FROM sqlite_master WHERE type='index' AND name=?", [$indexName]));
        }
        try {
            return !empty(DB::select("SELECT indexname FROM pg_indexes WHERE tablename = ? AND indexname = ?", [$table, $indexName]));
        } catch (\Throwable $e) {
            try { return !empty(DB::select("SHOW INDEX FROM `{$table}` WHERE Key_name = ?", [$indexName])); }
            catch (\Throwable $e2) { return false; }
        }
    }
};
