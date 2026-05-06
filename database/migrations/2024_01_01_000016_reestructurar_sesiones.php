<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // ── 1. Agregar columnas nuevas a sesiones_mesa ──────────────────
        Schema::table('sesiones_mesa', function (Blueprint $table) {
            $table->string('codigo_grupo', 10)->nullable()->after('mesa_id');
            $table->string('tipo_sesion', 20)->default('INDIVIDUAL')->after('codigo_grupo'); // INDIVIDUAL | COMPARTIDA
            $table->string('motivo_cierre', 20)->nullable()->after('estado');                // manual | inactividad
            $table->unsignedInteger('participantes_activos')->default(1)->after('motivo_cierre');
        });

        // ── 2. Modificar pedidos: quitar FK a sub_sesiones ──────────────
        Schema::table('pedidos', function (Blueprint $table) {
            // Eliminar FK existente
            $table->dropForeign(['sub_sesion_id']);
            // Renombrar columna
            $table->renameColumn('sub_sesion_id', 'sesion_mesa_id');
        });

        // ── 3. Agregar nueva FK de pedidos → sesiones_mesa ─────────────
        Schema::table('pedidos', function (Blueprint $table) {
            $table->foreign('sesion_mesa_id')
                  ->references('id')
                  ->on('sesiones_mesa')
                  ->onDelete('cascade');
        });

        // ── 4. Eliminar tabla sub_sesiones ──────────────────────────────
        Schema::dropIfExists('sub_sesiones');
    }

    public function down(): void
    {
        // Recrear sub_sesiones
        Schema::create('sub_sesiones', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('sesion_mesa_id');
            $table->unsignedBigInteger('cliente_id')->nullable();
            $table->string('estado', 20)->default('ACTIVA');
            $table->timestamp('fecha_inicio')->useCurrent();
            $table->timestamp('fecha_cierre')->nullable();
            $table->timestamps();
            $table->foreign('sesion_mesa_id')->references('id')->on('sesiones_mesa')->onDelete('cascade');
        });

        // Revertir pedidos
        Schema::table('pedidos', function (Blueprint $table) {
            $table->dropForeign(['sesion_mesa_id']);
            $table->renameColumn('sesion_mesa_id', 'sub_sesion_id');
            $table->foreign('sub_sesion_id')->references('id')->on('sub_sesiones')->onDelete('cascade');
        });

        // Quitar columnas de sesiones_mesa
        Schema::table('sesiones_mesa', function (Blueprint $table) {
            $table->dropColumn(['codigo_grupo', 'tipo_sesion', 'motivo_cierre', 'participantes_activos']);
        });
    }
};